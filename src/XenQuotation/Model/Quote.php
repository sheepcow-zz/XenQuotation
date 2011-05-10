<?php

/**
 * This is the model for the quotes. It provides the access
 * to the quotes.
 */
class XenQuotation_Model_Quote extends XenForo_Model
{

	public function getQuoteById($quoteId, array $fetchOptions = array())
	{
		return false;
	}
	
	public function getQuotesByIds(array $quoteIds, array $fetchOptions = array())
	{
		return false;
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
	
}

?>