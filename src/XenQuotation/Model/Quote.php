<?php

/**
 * This is the model for the quotes. It provides the access
 * to the quotes.
 */
class XenQuotation_Model_Quote extends XenForo_Model
{

	protected $_bbCodeParser = null;

	/**
	 * Gets quotes using the given fetch options
	 *
	 * @param array $fetchOptions Quote fetch options
	 *
	 * @return array
	 */
	public function getQuotes(array $fetchOptions = array())
	{		
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		$stateLimit = $this->prepareStateLimitFromConditions($fetchOptions, 'quotation', 'quote_state', 'author_user_id');
		$orderBy = $this->prepareQuoteOrderOptions($fetchOptions);
		
		$whereConditions = array();

		if (!empty($fetchOptions['authors']))
		{
			$authorIds = array();
			foreach ($fetchOptions['authors'] as $authorId)
			{
				$authorIds[] = '(quotation.author_user_id = ' . intval($authorId) . ')';
			}
			
			$whereConditions[] = implode(' OR ', $authorIds);
		}
		
		$whereClause = $this->getConditionsForClause($whereConditions);
				
		return $this->fetchAllKeyed($this->limitQueryResults('
				SELECT quotation.*
				FROM xq_quotation AS quotation
				WHERE (' . $stateLimit . ') AND (' . $whereClause . ')
				' . $orderBy . '
			', $limitOptions['limit'], $limitOptions['offset']), 
		'quote_id');
	}
	
	/**
	 */
	public function countQuotes(array $fetchOptions = array())
	{
		$stateLimit = $this->prepareStateLimitFromConditions($fetchOptions, 'quotation', 'quote_state', 'author_user_id');
		
		$whereConditions = array();

		if (!empty($fetchOptions['authors']))
		{
			$authorIds = array();
			foreach ($fetchOptions['authors'] as $authorId)
			{
				$authorIds[] = '(quotation.author_user_id = ' . intval($authorId) . ')';
			}
			
			$whereConditions[] = implode(' OR ', $authorIds);
		}
		
		$whereClause = $this->getConditionsForClause($whereConditions);
		
		return $this->_getDb()->fetchOne('
				SELECT COUNT(*)
				FROM xq_quotation AS quotation
				WHERE (' . $stateLimit . ') AND (' . $whereClause . ')
				ORDER BY quotation.quote_date DESC, quotation.quote_id DESC
			');
	}

	/**
	 * Gets the specified quote by ID.
	 *
	 * @param integer $quoteId
	 * @param array $fetchOptions Quote fetch options
	 */
	public function getQuoteById($quoteId, array $fetchOptions = array())
	{
		if (empty($quoteId))
		{
			return false;
		}
		
		return $this->_getDb()->fetchRow('
			SELECT quotation.* FROM xq_quotation AS quotation
			 WHERE quotation.`quote_id` = ?
		', $quoteId);
	}
	
	/**
	 * Gets quotations with the specified IDs.
	 *
	 * @param array $quoteIds
	 * @param array $fetchOptions Quote fetch options
	 */
	public function getQuotesByIds(array $quoteIds, array $fetchOptions = array())
	{
		if (!$quoteIds)
		{
			return array();
		}
		
		$orderClause = $this->prepareQuoteOrderOptions($fetchOptions, 'quotation.quote_date');
		
		return $this->fetchAllKeyed(
			'SELECT quotation.* FROM xq_quotation AS quotation
			 WHERE quotation.`quote_id` IN (' . $this->_getDb()->quote($quoteIds) . ') 
			' . $orderClause . '
		', 'quote_id');
	}
	
	/**
	 * Gets quote IDs in the specified range. The IDs returned will be those immediately
	 * after the "start" value (not including the start), up to the specified limit.
	 *
	 * @param integer $start IDs greater than this will be returned
	 * @param integer $limit Number of posts to return
	 *
	 * @return array List of IDs
	 */
	public function getQuoteIdsInRange($start, $limit)
	{
		$db = $this->_getDb();

		return $db->fetchCol($db->limit('
			SELECT quote_id
			FROM xq_quotation
			WHERE quote_id > ?
			ORDER BY quote_id
		', $limit), $start);
	}
	
	/**
	 * 
	 */
	public function quotationViewed(array &$quote)
	{
		$quote['views']++;
		
		$this->_getDb()->query('UPDATE xq_quotation SET views = views + 1 WHERE quote_id = ?', $quote['quote_id']);
	}
	
	/**
	 * 
	 */
	public function quotationsViewed(array &$quotes)
	{
		$quoteIds = array();
		foreach ($quotes as $q => $quote)
		{
			$quotes[$q]['views']++;
			$quoteIds[] = intval($quote['quote_id']);
		}
		
		if (count($quoteIds) > 0)
		{
			$this->_getDb()->query('UPDATE xq_quotation SET views = views + 1 WHERE quote_id IN (' . implode(', ', $quoteIds) . ')');
		}
	}
	
	/**
	 */
	public function addInlineModOptionToQuote(array &$quote, array $permissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		$modOptions = array();
		
		$canInlineMod = ($viewingUser['user_id'] && (
			XenForo_Permission::hasPermission($permissions, 'quote', 'deleteAny')
			|| XenForo_Permission::hasPermission($permissions, 'quote', 'undelete')
			|| XenForo_Permission::hasPermission($permissions, 'quote', 'approveUnapprove')
		));
		
		if ($canInlineMod)
		{
			if ($this->canDeleteQuote($quote, 'soft', $permissions, $viewingUser))
			{
				$modOptions['delete'] = true;
			}
			
			if ($this->canUndeleteQuote($quote, $permissions, $viewingUser))
			{
				$modOptions['undelete'] = true;
			}
			
			if ($this->canApproveUnapprove($quote, $permissions, $viewingUser))
			{
				$modOptions['approve'] = true;
				$modOptions['unapprove'] = true;
			}
		}
		
		$quote['canInlineMod'] = (count($modOptions) > 0);

		return $modOptions;
	}
	
	/**
	 */
	public function prepareQuotation(array &$quote)
	{
		
		$quote['attributed_user'] = array(
			'username' => $quote['attributed_username'],
			'user_id' => $quote['attributed_user_id']
		);
		
		$quote['author'] = array(
			'username' => $quote['author_username'],
			'user_id' => $quote['author_user_id']
		);
		
		if ($quote['quote_state'] == 'moderated')
		{
			$quote['isModerated'] = true;
		}
		
		if ($quote['quote_state'] == 'deleted')
		{
			$quote['isDeleted'] = true;
		}
		
		$bbCodeParser = $this->_getBbCodeParser();
		
		// bbcode parse the quote
		$quote['parsedQuotation'] = $this->_bbCodeParser->render(
			$quote['quotation'], 
			array(
				'stopLineBreakConversion' => true // stops new lines being rendered as <br/>
			)
		);

		$attribution = array();
		
		if (strlen($quote['attributed_user']['username']) > 0)
		{
			// attributed username is filled in, get the template helper
			// to render the username	
			$attribution[] = XenForo_Template_Helper_Core::helperUserName($quote['attributed_user']);
		}
		
		if ($quote['attributed_date'] > 0)
		{
			$attribution[] = '<span>' . XenForo_Template_Helper_Core::date($quote['attributed_date']) . '</span>';
		}
		
		if ($quote['attributed_post_id'] != 0)
		{
			$postModel = $this->getModelFromCache('XenForo_Model_Post');
			$post = $postModel->getPostById($quote['attributed_post_id']);
			
			if ($post)
			{
				$thread = $this->getModelFromCache('XenForo_Model_Thread')->getThreadById($post['thread_id']);
				
				// TODO: only allow the thread title to be displayed
				// if they have permission to see the thread
				
				if ($thread)
				{
					$threadTitle = new XenForo_Phrase(
						'xenquote_post_x_in_y',
						array(
							'position' => $post['position'] + 1,
							'title' => $thread['title']
						)
					);					
				}
				else
				{
					$threadTitle = new XenForo_Phrase(
						'xenquote_post_x', 
						array('post' => $post['post_id'])
					);
				}
				
				$attribution[] = '<a href="' .
				 	XenForo_Link::buildPublicLink('full:posts', array('post_id' => $post['post_id'])) . 
				'">' . $threadTitle . '</a>';
			}
		}
		
		if (strlen(trim($quote['attributed_context'])) > 0)
		{
			$attribution[] = '<span class="context">' . $quote['attributed_context'] . '</span>';
		}
		
		$quote['renderedAttribution'] = implode(', ', $attribution);
		
		return $quote;
	}
	
	/**
	 * Gets quote fetch options for the given user (or the active visitor)
	 * based on their permissions.
	 */
	public function getPermissionBasedQuoteFetchOptions(array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		$viewModerated = XenForo_Permission::hasPermission($viewingUser['permissions'], 'quote', 'viewModerated');
		$viewDeleted = XenForo_Permission::hasPermission($viewingUser['permissions'], 'quote', 'viewDeleted');
		
		return array(
			'moderated' => ($viewModerated === true) ? true : $viewingUser['user_id'],
			'deleted' => ($viewDeleted === true) ? true : $viewingUser['user_id']
		);
	}

	/**
	 * Indicates if the user is able to view the specified quotation
	 */
	public function canViewQuotation($quoteId, &$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		$quote = $this->getQuoteById($quoteId);
		
		if (!$quote)
		{
			/* no such quote */
			$errorPhraseKey = 'xenquote_requested_quotation_not_found';
			return false;
		}
		
		/* TODO: look at the state the quote is in */
		
		return XenForo_Permission::hasPermission($viewingUser['permissions'], 'quote', 'view');
	}
	
	/**
	 * Indicates if the user is able to add a quotation.
	 */
	public function canAddQuotation(&$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		if (!$viewingUser['user_id'])
		{
			$errorPhraseKey = 'xenquote_you_may_not_add_a_new_quotation';
			return false;
		}
		
		return XenForo_Permission::hasPermission($viewingUser['permissions'], 'quote', 'post');
	}
	
	/**
	 */
	public function canDeleteQuote(array $quote, $deleteType = 'soft', array $permissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		if (!$viewingUser['user_id'])
		{
			return false;
		}
		
		if ($deleteType != 'soft' && !XenForo_Permission::hasPermission($permissions, 'quote', 'hardDeleteAny'))
		{
			// fail immediately on hard delete without permission
			return false;
		}
		
		if (XenForo_Permission::hasPermission($permissions, 'quote', 'deleteAny'))
		{
			return true;
		}
		else if ($quote['author_user_id'] == $viewingUser['user_id'] &&
				 XenForo_Permission::hasPermission($permissions, 'quote', 'deleteOwn'))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 */
	public function canUndeleteQuote(array $quote, array $permissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		return ($viewingUser['user_id']) && XenForo_Permission::hasPermission($permissions, 'quote', 'undelete');
	}
	
	/**
	 */
	public function canApproveUnapprove(array $quote, array $permissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		return ($viewingUser['user_id']) && XenForo_Permission::hasPermission($permissions, 'quote', 'approveUnapprove');
	}
	
	/**
	 * Construct 'ORDER BY' clause
	 *
	 * @param array $fetchOptions (uses 'order' key)
	 * @param string $defaultOrderSql Default order SQL
	 *
	 * @return string
	 */
	public function prepareQuoteOrderOptions(array &$fetchOptions, $defaultOrderSql = 'quotation.quote_date')
	{
		$choices = array(
			'author' => 'quotation.author_username',
			'date' => 'quotation.quote_date',
			'attributed_date' => 'quotation.attributed_date',
			'attributed_user' => 'quotation.attributed_username',
			'views' => 'quotation.views',
			'likes' => 'quotation.likes'
		);
		return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
	}

	/**
	 * Gets the restricted BB code parser.
	 *
	 * @return XenForo_BbCode_Parser
	 */
	protected function _getBbCodeParser()
	{
		if (!$this->_bbCodeParser)
		{
			$this->_bbCodeParser = new XenForo_BbCode_Parser(new XenQuotation_BbCode_Formatter_Restricted());
		}
		
		return $this->_bbCodeParser;
	}
	
}

?>