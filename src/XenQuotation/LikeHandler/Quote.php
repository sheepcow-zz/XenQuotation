<?php

/**
 * Like handler for quotes.
 */
class XenQuotation_LikeHandler_Quote extends XenForo_LikeHandler_Abstract
{
	
	/**
	 * Increments the like counter.
	 * @see XenForo_LikeHandler_Abstract::incrementLikeCounter()
	 */
	public function incrementLikeCounter($contentId, array $latestLikes, $adjustAmount = 1)
	{
	}
	
	/**
	 * Gets content data (if viewable).
	 * @see XenForo_LikeHandler_Abstract::getContentData()
	 */
	public function getContentData(array $contentIds, array $viewingUser)
	{
		return array();
	}
	
}

?>