<?php

/**
 * This is the model for the quotes. It provides the access
 * to the quotes.
 */
class XenQuotation_Model_Quote extends XenForo_Model
{

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
		
		return $this->_getDb()->fetchRow(
			'SELECT quotation.* FROM xq_quotation AS quotation
			 WHERE quotation.`quote_id` = ?',
			$quoteId
		);
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
		
		return $quote;
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
	 * Construct 'ORDER BY' clause
	 *
	 * @param array $fetchOptions (uses 'order' key)
	 * @param string $defaultOrderSql Default order SQL
	 *
	 * @return string
	 */
	public function prepareQuoteOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
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
	
}

?>