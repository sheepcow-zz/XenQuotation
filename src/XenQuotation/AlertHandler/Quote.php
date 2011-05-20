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
 * Handles alerts of quotes.
 */
class XenQuotation_AlertHandler_Quote extends XenForo_AlertHandler_DiscussionMessage
{
	protected $_quoteModel = null;
	
	/**
	 * Gets the quote content.
	 * @see XenForo_AlertHandler_Abstract::getContentByIds()
	 */
	public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
	{
		$quoteModel = $this->_getQuoteModel();
		$quotes = $quoteModel->getQuotesByIds($contentIds);
		
		return $quotes;
	}
	
	/**
	 * Determines if the post is viewable.
	 * @see XenForo_AlertHandler_Abstract::canViewAlert()
	 */
	public function canViewAlert(array $alert, $content, array $viewingUser)
	{
		return $this->_getQuoteModel()->canViewQuotation(
			$content['quote_id'], $null, $viewingUser
		);
	}
	
	/**
	 * @return XenQuotation_Model_Quote
	 */
	protected function _getQuoteModel()
	{
		if (!$this->_quoteModel)
		{
			$this->_quoteModel = XenForo_Model::create('XenQuotation_Model_Quote');
		}

		return $this->_quoteModel;
	}
		
}

?>