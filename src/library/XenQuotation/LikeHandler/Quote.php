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
		$dw = XenForo_DataWriter::create('XenQuotation_DataWriter_Quote');
		$dw->setExistingData($contentId);
		$dw->set('likes', $dw->get('likes') + $adjustAmount);
		$dw->set('like_users', $latestLikes);
		$dw->save();
	}
	
	/**
	 * Gets content data (if viewable).
	 * @see XenForo_LikeHandler_Abstract::getContentData()
	 */
	public function getContentData(array $contentIds, array $viewingUser)
	{
		$quoteModel = XenForo_Model::create('XenQuotation_Model_Quote');
		$quotes = $quoteModel->getQuotesByIds($contentIds);
		
		foreach ($quotes as $key => &$quote)
		{
			if (!$quoteModel->canViewQuotation($quote['quote_id'], $errorPhraseKey, $viewingUser))
			{
				unset($quotes[$key]);
			}
			
			$quoteModel->prepareQuotation($quote);
		}
		
		return $quotes;
	}
	
	/**
	 * Gets the name of the template that will be used when listing likes of this type.
	 *
	 * @return string
	 */
	public function getListTemplateName()
	{
		return 'xenquote_news_feed_item_quotation_list';
	}
	
}

?>