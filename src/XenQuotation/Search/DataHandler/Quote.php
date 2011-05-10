<?php

/**
 * Search data handler for quotes.
 */
class XenQuotation_Search_DataHandler_Quote extends XenForo_Search_DataHandler_Abstract
{
	/**
	 * Inserts into (or replaces a record) in the index.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::_insertIntoIndex()
	 */
	protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
	{
	}
	
	/**
	 * Updates a record in the index.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::_updateIndex()
	 */
	protected function _updateIndex(XenForo_Search_Indexer $indexer, array $data, array $fieldUpdates)
	{
	}
	
	/**
	 * Deletes one or more records from the index.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::_deleteFromIndex()
	 */
	protected function _deleteFromIndex(XenForo_Search_Indexer $indexer, array $dataList)
	{
	}
	
	/**
	 * Rebuilds the index for a batch.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::rebuildIndex()
	 */
	public function rebuildIndex(XenForo_Search_Indexer $indexer, $lastId, $batchSize)
	{
	}
	
	/**
	 * Rebuilds the index for the specified content.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::quickIndex()
	 */
	public function quickIndex(XenForo_Search_Indexer $indexer, array $contentIds)
	{
	}
	
	/**
	 * Gets the type-specific data for a collection of results of this content type.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getDataForResults()
	 */
	public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
	{
		return array();
	}
	
	/**
	 * Determines if this result is viewable.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::canViewResult()
	 */
	public function canViewResult(array $result, array $viewingUser)
	{
		return false;
	}
	
	/**
	 * Prepares a result for display.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::prepareResult()
	 */
	public function prepareResult(array $result, array $viewingUser)
	{
		return $result;
	}
	
	/**
	 * Gets the date of the result (from the result's content).
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getResultDate()
	 */
	public function getResultDate(array $result)
	{
		return 0;
	}
	
	/**
	 * Renders a result to HTML.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::renderResult()
	 */
	public function renderResult(XenForo_View $view, array $result, array $search)
	{
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
		return null;
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
	
}

?>