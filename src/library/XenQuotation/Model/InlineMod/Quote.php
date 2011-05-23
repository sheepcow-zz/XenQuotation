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

class XenQuotation_Model_InlineMod_Quote extends XenForo_Model
{
	/**
	 * Approves the specified quotes if permissions are sufficient.
	 *
	 * @param array $quoteIds List of quotation IDs to approve
	 * @param array $options Options that control the action. Nothing supported at this time.
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser
	 *
	 * @return boolean True if permissions were ok
	 */
	public function approveQuotes(array $quoteIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
	{
		$quoteModel = $this->_getQuoteModel();
		
		XenForo_Helper_File::log('xenquotation', 'quoteIds: '.var_export($quoteIds, true));

		if (!empty($options['skipPermissions']) && !$quoteModel->canApproveUnapproveQuotes($viewingUser))
		{
			return false;
		}
		
		$fetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions($viewingUser);
		
		$quotes = $quoteModel->getQuotesByIds($quoteIds, $fetchOptions);
		$this->_updateQuotesState($quotes, 'visible', 'moderated');

		return true;
	}
	
	/**
	 * Unapproves the specified quotes if permissions are sufficient.
	 *
	 * @param array $quoteIds List of quotation IDs to unapprove
	 * @param array $options Options that control the action. Nothing supported at this time.
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser
	 *
	 * @return boolean True if permissions were ok
	 */
	public function unapproveQuotes(array $quoteIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
	{
		$quoteModel = $this->_getQuoteModel();

		if (!empty($options['skipPermissions']) && !$quoteModel->canApproveUnapproveQuotes($viewingUser))
		{
			return false;
		}
		
		$fetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions($viewingUser);
		
		$quotes = $quoteModel->getQuotesByIds($quoteIds, $fetchOptions);
		$this->_updateQuotesState($quotes, 'moderated', 'visible');

		return true;
	}
	
	/**
	 * Undeletes the specified quotes if permissions are sufficient.
	 *
	 * @param array $quoteIds List of quotation IDs to unapprove
	 * @param array $options Options that control the action. Nothing supported at this time.
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser
	 *
	 * @return boolean True if permissions were ok
	 */
	public function undeleteQuotes(array $quoteIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
	{
		$quoteModel = $this->_getQuoteModel();

		if (!empty($options['skipPermissions']) && !$quoteModel->canUndeleteQuotes($viewingUser))
		{
			return false;
		}
		
		$fetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions($viewingUser);
		
		$quotes = $quoteModel->getQuotesByIds($quoteIds, $fetchOptions);
		$this->_updateQuotesState($quotes, 'visible', 'deleted');

		return true;
	}
	
	/**
	 * Deletes the specified quotes if permissions are sufficient.
	 *
	 * @param array $quoteIds List of quotation IDs to unapprove
	 * @param array $options Options that control the action. Nothing supported at this time.
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser
	 *
	 * @return boolean True if permissions were ok
	 */
	public function deleteQuotes(array $quoteIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
	{
		$quoteModel = $this->_getQuoteModel();
		
		$fetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions($viewingUser);
		$quotes = $quoteModel->getQuotesByIds($quoteIds, $fetchOptions);
		
		if (!empty($options['deleteType']) && $options['deleteType'] == 'hard')
		{
			$deleteType = 'hard';
		}
		else
		{
			$deleteType = 'soft';
		}

		if (!empty($options['skipPermissions']))
		{
			foreach ($quotes as $quoteId => $quote)
			{
				if (!$quoteModel->canDeleteQuote($quote, $deleteType, $viewingUser))
				{
					return false;
				}
			}
		}
		
		if ($deleteType == 'soft')
		{
			$this->_updateQuotesState($quotes, 'deleted');
		}
		else
		{
			// TODO: support hard delete
			$this->_updateQuotesState($quotes, 'deleted');
		}

		return true;
	}
	
	/**
	 * @param array $quotes Quotes to update the state of
	 * @param string $newState New quotation state (visible, moderated, deleted)
	 * @param string|false $expectedOldState If specified, only updates if the old state matches
	 */
	protected function _updateQuotesState(array $quotes, $newState, $expectedOldState = false)
	{
		
		XenForo_Helper_File::log('xenquotation', var_export($quotes, true));
		
		foreach ($quotes AS $quote)
		{
			if ($expectedOldState && $quote['quote_state'] != $expectedOldState)
			{
				continue;
			}

			$dw = XenForo_DataWriter::create('XenQuotation_DataWriter_Quote', XenForo_DataWriter::ERROR_SILENT);
			$dw->setExistingData($quote);
			$dw->set('quote_state', $newState);
			$dw->save();
		}
	}
	
	/**
	 * @return XenQuotation_Model_Quote
	 */
	protected function _getQuoteModel()
	{
		return $this->getModelFromCache('XenQuotation_Model_Quote');
	}
}

?>