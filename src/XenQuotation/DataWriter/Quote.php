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
 * The DataWriter class deals with validating and writing
 * quotes to the database.
 */
class XenQuotation_DataWriter_Quote extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'xenquote_requested_quotation_not_found';
	
	protected function _getFields()
	{
		return array(
			'xq_quotation' => array(
				'quote_id'				=> array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'author_user_id'		=> array('type' => self::TYPE_UINT, 'default' => 0),
				'author_username'		=> array('type' => self::TYPE_STRING, 'default' => ''),
				'quote_date'			=> array('type' => self::TYPE_UINT, 
												 'default' => XenForo_Application::$time),
				'quotation'				=> array('type' => self::TYPE_STRING, 'required' => true,
												 'requiredError' => 'xenquote_you_must_enter_a_quotation'),
				'quote_state'			=> array('type' => self::TYPE_STRING, 'default' => 'moderated',
												 'allowedValues' => array('visible', 'moderated', 'deleted')),				
				'attributed_date'		=> array('type' => self::TYPE_UINT, 'default' => 0),
				'attributed_context'	=> array('type' => self::TYPE_STRING, 'default' => ''),
				'attributed_post_id'	=> array('type' => self::TYPE_UINT, 'default' => 0),
				'attributed_user_id'	=> array('type' => self::TYPE_UINT, 'default' => 0),
				'attributed_username'	=> array('type' => self::TYPE_STRING, 'default' => ''),
				'views'					=> array('type' => self::TYPE_UINT, 'default' => 0),
				'likes'					=> array('type' => self::TYPE_UINT, 'default' => 0),
				'like_users'			=> array('type' => self::TYPE_SERIALIZED, 'default' => 'a:0:{}'),
		));
	}

	protected function _getExistingData($data)
	{
		if (!$quoteId = $this->_getExistingPrimaryKey($data, 'quote_id'))
		{
			return false;
		}

		return array(
			'xq_quotation' => $this->getModelFromCache('XenQuotation_Model_Quote')->getQuoteById(
				$quoteId, array('quoteStates' => array('new', 'approved', 'rejected'))
			)
		);
	}

	protected function _getUpdateCondition($tableName)
	{
		return '`quote_id`=' . $this->_db->quote($this->getExisting('quote_id'));
	}
	
	protected function _preSave()
	{
	}
	
	/**
	 * Alerts the attributed user that a quotation has
	 * been added about them.
	 */
	protected function _postSave()
	{
		$this->_addToSearchIndex();
		
		if ($this->get('message_state') == 'visible')
		{
			/* TODO */
		}
	}
	
	/**
	 * Removes any alerts for a quote that has been deleted
	 */
	protected function _postDelete()
	{
		$this->_removeFromSearchIndex();
		
		if ($this->get('quote_state') == 'visible')
		{
			$this->getModelFromCache('XenForo_Model_Alert')->deleteAlerts('quote', $this->get('quote_id'));
		}
	}
	
	/**
	 */
	protected function _addToSearchIndex()
	{
		if ($this->isChanged('quotation'))
		{
			$dataHandler = $this->_getSearchDataHandler();
			$indexer = new XenForo_Search_Indexer();

			$dataHandler->insertIntoIndex($indexer, $this->getMergedData());
		}
	}
	
	/**
	 */
	protected function _removeFromSearchIndex()
	{
		$dataHandler = $this->_getSearchDataHandler();
		$indexer = new XenForo_Search_Indexer();

		$dataHandler->deleteFromIndex($indexer, $this->getMergedData());
	}
	
	/**
	 * @return XenQuotation_Search_DataHandler_Quote
	 */
	protected function _getSearchDataHandler()
	{
		// Gets the search-data handler for 'quote' content type
		return $this->getModelFromCache('XenForo_Model_Search')->getSearchDataHandler('quote');
	}
	
}

?>