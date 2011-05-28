<?php
/**
 * Copyright 2011 Ben O'Neill
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Controller for the quotes.
 */
class XenQuotation_ControllerPublic_Quote extends XenForo_ControllerPublic_Abstract
{
	
	/**
	 * Reroutes to either display a list of all the quotes or, if 
	 * a quote id has been supplied, view a specific quote.
	 */
	public function actionIndex()
	{
		
		if ($this->_input->inRequest('quote_id'))
		{
			// a quote id has been supplied, redirect to view the quote
			return $this->responseReroute(__CLASS__, 'view');
		}
		
		// otherwise, redirect to the quotes list
		return $this->responseReroute(__CLASS__, 'list');
	}
	
	/**
	 * Displays the basic list of quotes to the user.
	 */
	public function actionList()
	{
		$quotesByUser = false;
		
		// default sorting
		$defaultSortOrder = 'date';
		$defaultSortDirection = 'desc';
		
		$sortOrder = $defaultSortOrder;
		$sortDirection = $defaultSortDirection;
		
		$additionalNavParams = array();
		
		if ($this->_input->inRequest('user'))
		{
			$userModel = $this->_getUserModel();
			$userId = $this->_input->filterSingle('user', XenForo_Input::UINT);
			$quotesByUser = $userModel->getUserById($userId);
		}
		
		$visitor = XenForo_Visitor::getInstance();
		
		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$quotesPerPage = XenForo_Application::get('options')->xenquoteQuotationsPerPage;
		
		$quoteModel = $this->_getQuoteModel();
		
		// work out any sorting
		$input = $this->_input->filter(array(
			'order' => XenForo_Input::STRING,
			'direction' => XenForo_Input::STRING
		));
		
		if (!empty($input['order']))
		{
			switch (strtolower($input['order']))
			{
				case 'date':
				case 'likes':
				case 'views':
					$sortOrder = $input['order'];
					break;
			}
		}
		
		if (!empty($input['direction']))
		{
			switch (strtolower($input['direction']))
			{
				case 'desc':
					$sortDirection = 'desc';
					break;
					
				case 'asc':
					$sortDirection = 'asc';
					break;
			}
		}
		
		// check they can actually view any quotations
		$quoteHelper = $this->getHelper('XenQuotation_ControllerHelper_Quote');
		$quoteHelper->assertCanViewQuotes();
		
		$orderParams = array();
		
		$quoteFetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions() + array(
			'perPage' => $quotesPerPage,
			'page' => $page,
			'order' => $sortOrder,
			'direction' => $sortDirection,
			'likeUserId' => $visitor['user_id']
		);
		
		if (!empty($quotesByUser))
		{
			// displaying a list of quotes by a specific user
			$additionalNavParams['user'] = $quotesByUser['user_id'];
			
			$quoteFetchOptions += array(
				'authors' => array($quotesByUser['user_id'])
			);
		}
		
		foreach (array('date', 'likes', 'views') AS $field)
		{
			$sortParams[$field]['order'] = ($field != $defaultSortOrder ? $field : false);
			if ($sortOrder == $field)
			{
				$sortParams[$field]['direction'] = ($sortDirection == 'desc' ? 'asc' : 'desc');
			}
			
			$sortParams[$field] += $additionalNavParams;
		}
		
		$quotes = $quoteModel->getQuotes($quoteFetchOptions);
		$totalQuotations = $quoteModel->countQuotes($quoteFetchOptions);
		
		// prepare all quotes for the quote list
		$inlineModOptions = array();
		
		foreach ($quotes as &$quote)
		{
			
			$quoteModOptions = $quoteModel->addInlineModOptionToQuote($quote);
			$inlineModOptions += $quoteModOptions;
			
			if (!empty($quoteModOptions['approve']) && $quote['quote_state'] == 'moderated')
			{
				$quote['canApprove'] = true;
			}
			
			if (!empty($quoteModOptions['unapprove']) && $quote['quote_state'] == 'visible')
			{
				$quote['canUnapprove'] = true;
			}
			
			if (!empty($quoteModOptions['delete']) && $quote['quote_state'] != 'deleted')
			{
				$quote['canDelete'] = true;
			}
			
			if (!empty($quoteModOptions['undelete']) && $quote['quote_state'] == 'deleted')
			{
				$quote['canUndelete'] = true;
			}
			
			if ($quoteModel->canLikeQuotation($quote))
			{
				$quote['canLike'] = true;
			}
			
			$quoteModel->prepareQuotation($quote);

		}
		unset($quote);
		
		$quoteModel->quotationsViewed($quotes);
		
		$viewParams = array(
			'page' => $page,

			'quotationsPerPage' => $quotesPerPage,
			'totalQuotations' => $totalQuotations,
			
			'quotationStartOffset' => ($page - 1) * $quotesPerPage + 1,
			'quotationEndOffset' => ($page - 1) * $quotesPerPage + count($quotes),

			'canCreateQuotation' => $quoteModel->canAddQuotation(),
			'quotesByUser' => ($quotesByUser) ? $quotesByUser['username'] : false,
			'quotes' => $quotes,
			'inlineModOptions' => $inlineModOptions,
			
			'order' => $sortOrder,
			'orderDirection' => $sortDirection,
			'orderParams' => $sortParams,
			
			'showPostedNotice' => ($this->_input->filterSingle('posted', XenForo_Input::UINT) == 1),
			
			'pageNavParams' => array(
				'order' => ($sortOrder != $defaultSortOrder) ? $sortOrder : '',
				'direction' => ($sortDirection != $defaultSortDirection) ? $sortDirection : ''
			) + $additionalNavParams
		);
		
		return $this->responseView('XenQuotation_ViewPublic_Quote_List', 'xenquote_quote_list', $viewParams);
	}

	/**
	 * Viewing a specific quote
	 */	
	public function actionView()
	{
		$quoteModel = $this->_getQuoteModel();
		
		$quoteId = $this->_input->filterSingle('quote_id', XenForo_Input::UINT);
		
		$quoteHelper = $this->getHelper('XenQuotation_ControllerHelper_Quote');
		$quoteHelper->assertQuoteValidAndViewable($quoteId);
		
		/*TODO $this->_assertCanViewQuote($quoteId)*/
		
		$fetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions();
		
		$quote = $quoteModel->getQuoteById($quoteId, $fetchOptions);
		$quoteModel->prepareQuotation($quote);
		$quoteModel->quotationViewed($quote);
		
		$likesUsers = array();
		$totalLikes = 0;
		
		foreach ($quote['like_users'] as $likeUser)
		{
			$likesUsers[] = $likeUser['username'];
			$totalLikes++;
		}
		
		$likeList = '';
		
		switch ($totalLikes)
		{
			case 0: 
			break;
				
			case 1:
				$likeList = new XenForo_Phrase(
					'likes_user1_likes_this', 
					array('user1' => $likesUsers[0])
				);
			break;
				
			case 2:
				$likeList = new XenForo_Phrase(
					'likes_user1_and_user2_like_this', 
					array('user1' => $likesUsers[0], 'user2' => $likesUsers[1])
				);
			break;
			
			case 3:
				$likeList = new XenForo_Phrase(
					'likes_user1_user2_and_user3_like_this', 
					array('user1' => $likesUsers[0], 'user2' => $likesUsers[1], 'user3' => $likesUsers[2])
				);
			break;
			
			default:
				$lastUser = array_pop($likesUsers);
				$likeList = implode(', ', $likesUsers) . ' ';
				$likeList .= new XenForo_Phrase('xenquote_and_user_like_this', array(
					'user' => $lastUser
				));
			break;
		}
		
		$viewParams = array(
			'quote' => $quote,
			'likeList' => $likeList
		);
		
		return $this->responseView('XenQuotation_ViewPublic_Quote_List', 'xenquote_quote_view', $viewParams);	
	}
	
	/**
	 * Renders a preview of a specific quotation
	 */
	public function actionPreview()
	{
		$quoteId = $this->_input->filterSingle('quote_id', XenForo_Input::UINT);
		$quoteModel = $this->_getQuoteModel();

		$visitor = XenForo_Visitor::getInstance();
		
		$quoteHelper = $this->getHelper('XenQuotation_ControllerHelper_Quote');
		$quoteHelper->assertQuoteValidAndViewable($quoteId);

		$quote = $quoteModel->getQuoteById($quoteId);
		$quoteModel->prepareQuotation($quote);	
		
		$viewParams = array(
			'quote' => $quote
		);
		
		return $this->responseView('XenQuotation_ViewPublic_Quote_Preview', 'xenquote_list_item_preview', $viewParams);
	}
	
	/**
	 * Displays a form to allow users to edit quotes.
	 */
	public function actionEdit()
	{
		$viewParams = array();
		return $this->responseView('XenQuotation_ViewPublic_Quote_List', 'DEFAULT', $viewParams);
	}
	
	/**
	 * Shows a preview of the edit.
	 */
	public function actionEditPreview()
	{
		
		$visitor = XenForo_Visitor::getInstance();
		$quoteModel = $this->_getQuoteModel();
		
		$quote = array(
			'attributed_username' => '',
			'attributed_user_id' => 0,
			'attributed_context' => '',
			'attributed_post_id' => 0,
			'attributed_date' => 0,
			'likes' => 0,
			'views' => 0,
			'like_users' => 'a:0:{}',
			'author_user_id' => $visitor['user_id'],
			'author_username' => $visitor['username'],
			'quote_state' => 'visible'
		);
		
		$quote['quotation'] = $this->getHelper('Editor')->getMessageText('quotation', $this->_input);
		$quote['quotation'] = XenForo_Helper_String::autoLinkBbCode($quote['quotation']);

		$quoteModel->prepareQuotation($quote);
		
		$viewParams = array(
			'quote' => $quote
		);

		return $this->responseView(
			'XenQuotation_ViewPublic_Quote_EditPreview', 'xenquote_quote_edit_preview', $viewParams
		);
	}
	
	/**
	 * Creates a new post or saves the changes to an existing one.
	 */
	public function actionSave()
	{
		$this->_assertPostOnly();
		$this->_assertRegistrationRequired();
		
		$visitor = XenForo_Visitor::getInstance();
		
		$quoteHelper = $this->getHelper('XenQuotation_ControllerHelper_Quote');
		
		if ($this->_input->inRequest('quote_id'))
		{
			// editing a quote
			$quoteId = $this->_input->filterSingle('quote_id', XenForo_Input::UINT);
			$quoteHelper->assertQuoteValidAndViewable($quoteId);
			
			/* TODO */
			/*
			$this->_assertCanEditQuote($quoteId)
			
			$dw = XenForo_DataWriter::create('XenQuotation_DataWriter_Quote');
			$dw->setExistingData($quoteId);
			*/
		}
		else
		{
			$this->_assertCanAddQuotation();
			
			$dw = XenForo_DataWriter::create('XenQuotation_DataWriter_Quote');
			
			$dw->set('author_user_id', $visitor['user_id']);
			$dw->set('author_username', $visitor['username']);
		}
		
		if (!XenForo_Captcha_Abstract::validateDefault($this->_input))
		{
			return $this->responseCaptchaFailed();
		}
		
		// get the data
		$input = $this->_input->filter(array(
			'context' => XenForo_Input::STRING,
			'attributedDate' => XenForo_Input::DATE_TIME,
			'attributedTo' => XenForo_Input::STRING,
		));
		
		if ($this->_input->inRequest('post'))
		{
			$postId = $this->_input->filterSingle('post', XenForo_Input::UINT);
			
			$threadFetchOptions = array(
				'readUserId' => $visitor['user_id'],
				'watchUserId' => $visitor['user_id'],
				'join' => XenForo_Model_Thread::FETCH_AVATAR
			);
			$forumFetchOptions = array(
				'readUserId' => $visitor['user_id']
			);
			
			$postModel = $this->_getPostModel();
			$post = $postModel->getPostById($postId);
			
			if ($post)
			{
				$threadModel = $this->_getThreadModel();
				$thread = $threadModel->getThreadById($post['thread_id']);
				
				if ($thread)
				{
					$forumModel = $this->_getForumModel();
					$forum = $forumModel->getForumById($thread['node_id']);
					
					if ($forum)
					{
						if ($postModel->canViewPostAndContainer($post, $thread, $forum))
						{
							$input['attributedTo'] = $post['username'];
							$input['attributedDate'] = $post['post_date'];
							
							$dw->set('attributed_post_id', $postId);
						}
					}
				}
			}
		}
		
		// parse the attributedTo field to determine if it
		// is a forum username.
		
		if (strlen($input['attributedTo']) > 0)
		{
			if (preg_match('#^(.+)(,)?$#iU', $input['attributedTo'], $match))
			{
				$attribUser = $this->getModelFromCache('XenForo_Model_User')->getUserByName($match[1]);

				if ($attribUser)
				{
					$dw->set('attributed_user_id', $attribUser['user_id']);
					$dw->set('attributed_username', $attribUser['username']);
				}
				else
				{
					$dw->set('attributed_username', $match[1]);
				}
			}
		}
		
		$input['quotation'] = $this->getHelper('Editor')->getMessageText('quotation', $this->_input);
		$input['quotation'] = XenForo_Helper_String::autoLinkBbCode($input['quotation']);

		// set the data and save the changes
		$dw->set('quotation', $input['quotation']);
		
		$dw->set('attributed_date', $input['attributedDate']);
		$dw->set('attributed_context', $input['context']);
		$dw->save();
		
		$quote = $dw->getMergedData();
		
		if ($quote['quote_state'] != 'visible')
		{
			$params = array('posted' => 1);
		}
		else
		{
			$params = array();
		}

		// regular redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('quotes', '', $params),
			new XenForo_Phrase('xenquote_your_quotation_has_been_added')
		);
	}
	
	/**
	 * Create a new quote
	 */
	public function actionCreateQuote()
	{
		$this->_assertCanAddQuotation();
		
		$defaultMessage = '';
		$quote = false;
		
		$viewParams = array(
			'defaultMessage' => $defaultMessage,
			'captcha' => XenForo_Captcha_Abstract::createDefault(),
		);
		
		if ($this->_input->inRequest('post'))
		{
			$visitor = XenForo_Visitor::getInstance();
			$postId = $this->_input->filterSingle('post', XenForo_Input::UINT);
			
			$threadFetchOptions = array(
				'readUserId' => $visitor['user_id'],
				'watchUserId' => $visitor['user_id'],
				'join' => XenForo_Model_Thread::FETCH_AVATAR
			);
			$forumFetchOptions = array(
				'readUserId' => $visitor['user_id']
			);
			
			$postModel = $this->_getPostModel();
			$post = $postModel->getPostById($postId);
			
			if ($post)
			{
				$threadModel = $this->_getThreadModel();
				$thread = $threadModel->getThreadById($post['thread_id']);
				
				if ($thread)
				{
					$forumModel = $this->_getForumModel();
					$forum = $forumModel->getForumById($thread['node_id']);
					
					if ($forum)
					{
						if ($postModel->canViewPostAndContainer($post, $thread, $forum))
						{
							$viewParams += array(
								'forum' => $forum,
								'thread' => $thread,
								'post' => $post
							);
							
							$viewParams['defaultMessage'] = XenForo_Helper_String::stripQuotes($post['message'], 0);
						}
					}
				}
			}
			
		}
		
		return $this->responseView('XenQuotation_ViewPublic_Quote_Create', 'xenquote_quote_create', $viewParams);
	}
	
	/**
	 *
	 */
	public function actionLike()
	{
		$quoteId = $this->_input->filterSingle('quote_id', XenForo_Input::UINT);
		
		$quoteHelper = $this->getHelper('XenQuotation_ControllerHelper_Quote');
		$quote = $quoteHelper->assertQuoteValidAndViewable($quoteId);

		if (!$this->_getQuoteModel()->canLikeQuotation($quote, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		$likeModel = $this->_getLikeModel();

		$existingLike = $likeModel->getContentLikeByLikeUser('quote', $quoteId, XenForo_Visitor::getUserId());

		if ($this->_request->isPost())
		{
			if ($existingLike)
			{
				$latestUsers = $likeModel->unlikeContent($existingLike);
			}
			else
			{
				$latestUsers = $likeModel->likeContent('quote', $quoteId, $quote['author_user_id']);
			}

			$liked = ($existingLike ? false : true);

			if ($this->_noRedirect() && $latestUsers !== false)
			{
				$quote['likeUsers'] = $latestUsers;
				$quote['likes'] += ($liked ? 1 : -1);
				$quote['like_date'] = ($liked ? XenForo_Application::$time : 0);

				$viewParams = array(
					'quote' => $quote,
					'liked' => $liked,
				);

				return $this->responseView('XenQuotation_ViewPublic_Quote_LikeConfirmed', '', $viewParams);
			}
			else
			{
				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('quotes')
				);
			}
		}
		else
		{
			$viewParams = array(
				'quote' => $quote,
				'like' => $existingLike
			);

			return $this->responseView('XenQuotation_ViewPublic_Quote_Like', 'xenquote_post_like', $viewParams);
		}
	}
	
	/**
	 * Lists everyone that likes a particular quote.
	 */
	public function actionLikes()
	{
	}
	
	/**
	 * Session activity details.
	 * @see XenForo_Controller::getSessionActivityDetailsForList()
	 */
	public static function getSessionActivityDetailsForList(array $activities)
	{
		$output = array();
		$quoteModel = XenForo_Model::create('XenQuotation_Model_Quote');
		
		foreach ($activities as $key => $activity)
		{
			if ($activity['controller_action'] == 'List')
			{
				$output[$key] = new XenForo_Phrase('xenquote_viewing_quotations');
			}
			else if (!empty($activity['params']['quote_id']))
			{
				if ($quoteModel->canViewQuotation($activity['params']['quote_id']) &&
					($quote = $quoteModel->getQuoteById($activity['params']['quote_id'])))
				{
					$output[$key] = array(
						new XenForo_Phrase('xenquote_viewing_quotation'),
						new XenForo_Phrase('xenquote_added_by_x', 
							array('username' => $quote['author_username'])
						),
						XenForo_Link::buildPublicLink('quotes', $quote),
						XenForo_Link::buildPublicLink('quotes/preview', $quote)
					);
				}
				else
				{
					$output[$key] = new XenForo_Phrase('xenquote_viewing_a_quotation');
				}
			}
		}
		
		return $output;
	}
	
	/**
	 */
	protected function _assertCanAddQuotation()
	{		
		if (!$this->_getQuoteModel()->canAddQuotation($errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}
	}

	/**
	 * @return XenForo_Model_Like
	 */
	protected function _getLikeModel()
	{
		return $this->getModelFromCache('XenForo_Model_Like');
	}
	
	/**
	 * @return XenQuotation_Model_Quote
	 */
	protected function _getQuoteModel()
	{
		return $this->getModelFromCache('XenQuotation_Model_Quote');
	}
	
	/**
	 * @return XenForo_Model_User
	 */
	protected function _getUserModel()
	{
		return $this->getModelFromCache('XenForo_Model_User');
	}

	/**
	 * @return XenForo_Model_Forum
	 */
	protected function _getForumModel()
	{
		return $this->getModelFromCache('XenForo_Model_Forum');
	}
	
	/**
	 * @return XenForo_Model_Thread
	 */
	protected function _getThreadModel()
	{
		return $this->getModelFromCache('XenForo_Model_Thread');
	}
	
	/**
	 * @return XenForo_Model_Post
	 */
	protected function _getPostModel()
	{
		return $this->getModelFromCache('XenForo_Model_Post');
	}
}

?>