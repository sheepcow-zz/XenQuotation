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
 * Search data handler for quotes.
 */
class XenQuotation_Search_DataHandler_Quote extends XenForo_Search_DataHandler_Abstract
{
	
	/**
	 * @var XenQuotation_Model_Quote
	 */
	protected $_quoteModel = null;
	
	/**
	 * Inserts into (or replaces a record) in the index.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::_insertIntoIndex()
	 */
	protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
	{
		$metadata = array();
		$groupId = 0;
		$title = '';
		
		// index the quotation with the context and attributed username.
		$indexedData = $data['quotation'] . ' - ' . $data['attributed_context'] . ' - ' . $data['attributed_username'];
		
		$indexer->insertIntoIndex(
			'quote', $data['quote_id'],
			$title, $indexedData,
			$data['quote_date'], $data['author_user_id'], $groupId, $metadata
		);
	}
	
	/**
	 * Updates a record in the index.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::_updateIndex()
	 */
	protected function _updateIndex(XenForo_Search_Indexer $indexer, array $data, array $fieldUpdates)
	{
		$indexer->updateIndex('quote', $data['quote_id'], $fieldUpdates);
	}
	
	/**
	 * Deletes one or more records from the index.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::_deleteFromIndex()
	 */
	protected function _deleteFromIndex(XenForo_Search_Indexer $indexer, array $dataList)
	{
		$quoteIds = array();
		foreach ($dataList AS $data)
		{
			$quoteIds[] = $data['quote_id'];
		}

		$indexer->deleteFromIndex('quote', $quoteIds);
	}
	
	/**
	 * Rebuilds the index for a batch.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::rebuildIndex()
	 */
	public function rebuildIndex(XenForo_Search_Indexer $indexer, $lastId, $batchSize)
	{
		$quoteIds = $this->_getQuoteModel()->getQuoteIdsInRange($lastId, $batchSize);
		if (!$quoteIds)
		{
			return false;
		}

		$this->quickIndex($indexer, $quoteIds);

		return max($quoteIds);
	}
	
	/**
	 * Rebuilds the index for the specified content.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::quickIndex()
	 */
	public function quickIndex(XenForo_Search_Indexer $indexer, array $contentIds)
	{
		$quoteModel = $this->_getQuoteModel();

		$quotes = $quoteModel->getQuotesByIds($contentIds);

		foreach ($quotes AS $quote)
		{
			$this->insertIntoIndex($indexer, $quote);
		}

		return true;
	}
	
	/**
	 * Gets the type-specific data for a collection of results of this content type.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getDataForResults()
	 */
	public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
	{
		return $this->_getQuoteModel()->getQuotesByIds($ids, array(
			'join' => XenQuotation_Model_Quote::FETCH_AVATARS
		));
	}
	
	/**
	 * Determines if this result is viewable.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::canViewResult()
	 */
	public function canViewResult(array $result, array $viewingUser)
	{
		$quoteModel = $this->_getQuoteModel();
		
		return $quoteModel->canViewQuotation($result['quote_id']);
	}
	
	/**
	 * Prepares a result for display.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::prepareResult()
	 */
	public function prepareResult(array $result, array $viewingUser)
	{
		$this->_getQuoteModel()->prepareQuotation($result);
		return $result;
	}
	
	/**
	 * Gets the date of the result (from the result's content).
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getResultDate()
	 */
	public function getResultDate(array $result)
	{
		return $result['quote_date'];
	}
	
	/**
	 * Renders a result to HTML.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::renderResult()
	 */
	public function renderResult(XenForo_View $view, array $result, array $search)
	{
		return $view->createTemplateObject('xenquote_search_result_quote', array(
			'quote' => $result,
			'search' => $search,
		));
	}
	
	/**
	 * Gets the content types searched in a type-specific search.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getSearchContentTypes()
	 */
	public function getSearchContentTypes()
	{
		return array('quote');
	}
	
	/**
	 * Get type-specific constraints from input.
	 *
	 * @param XenForo_Input $input
	 *
	 * @return array
	 */
	public function getTypeConstraintsFromInput(XenForo_Input $input)
	{
		return array();
	}
	
	/**
	 * Process a type-specific constraint.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::processConstraint()
	 */
	public function processConstraint(XenForo_Search_SourceHandler_Abstract $sourceHandler, $constraint, $constraintInfo, array $constraints)
	{
		return false;
	}
	
	/**
	 * Gets the search form controller response for this type.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getSearchFormControllerResponse()
	 */
	public function getSearchFormControllerResponse(XenForo_ControllerPublic_Abstract $controller, XenForo_Input $input, array $viewParams)
	{
		return $controller->responseView('XenQuotation_ViewPublic_Search_Form_Quote', 'xenquote_search_form_quote', $viewParams);
	}
	
	/**
	 * Gets the search order for a type-specific search.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getOrderClause()
	 */
	public function getOrderClause($order)
	{
		return false;
	}
	
	/**
	 * Gets the necessary join structure information for this type.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getJoinStructures()
	 */
	public function getJoinStructures(array $tables)
	{
		return array();
	}
	
	/**
	 * Gets the content type that will be used when grouping for this type.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getGroupByType()
	 */
	public function getGroupByType()
	{
		return 'quote';
	}
	
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