<?php

/**
 * Handles alerts of quotes.
 */
class XenQuotation_AlertHandler_Quote extends XenForo_AlertHandler_DiscussionMessage
{
	/**
	 * Gets the quote content.
	 * @see XenForo_AlertHandler_Abstract::getContentByIds()
	 */
	public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
	{
		return array();
	}
	
	/**
	 * Determines if the post is viewable.
	 * @see XenForo_AlertHandler_Abstract::canViewAlert()
	 */
	public function canViewAlert(array $alert, $content, array $viewingUser)
	{
		return false;
	}
		
}

?>