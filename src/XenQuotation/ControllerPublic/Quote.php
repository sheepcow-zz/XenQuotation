<?php

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
		
		if ($this->_input->inRequest('user'))
		{
			$userModel = $this->_getUserModel();
			$userId = $this->_input->filterSingle('user', XenForo_Input::UINT);
			$quotesByUser = $userModel->getUserById($userId);
		}
		
		if ($quotesByUser)
		{
			// displaying a list of quotes by a specific user
		}
		else
		{
			// displaying all quotes
		}
		
		$visitor = XenForo_Visitor::getInstance();
		
		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$postsPerPage = XenForo_Application::get('options')->xenquoteQuotationsPerPage;
		
		$quoteModel = $this->_getQuoteModel();
		
		$quoteFetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions() + array(
			'perPage' => $postsPerPage,
			'page' => $page,
			'likeUserId' => $visitor['user_id']
		);
		
		$quotes = $quoteModel->getQuotes($quoteFetchOptions);
		
		foreach ($quotes as &$quote)
		{
			$quote = $quoteModel->prepareQuotation($quote);
		}
		
		$viewParams = array(
			'page' => 1,
			'canCreateQuotation' => $quoteModel->canAddQuotation(),
			'quotesByUser' => ($quotesByUser) ? $quotesByUser['username'] : false,
			'quotes' => $quotes
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
		
		$quote = $quoteModel->getQuoteById($quoteId);
		$quoteModel->prepareQuotation($quote);
		
		$viewParams = array(
			'quote' => $quote
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
			'attributedTo' => XenForo_Input::STRING
		));
		
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

		// regular redirect
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('quotes', $quote)
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
			'quote' => $quote
		);
		
		return $this->responseView('XenQuotation_ViewPublic_Quote_Create', 'xenquote_quote_create', $viewParams);
	}
	
	/**
	 *
	 */
	public function actionLike()
	{
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
						new XenForo_Phrase('xenquote_added_by', 
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
}

?>