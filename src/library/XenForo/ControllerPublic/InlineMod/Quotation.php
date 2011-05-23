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
 * This class is part of XenQuotation. It lives in the
 * XenForo directory so it can integrate with the XenForo
 * inline moderation controller.
 */
class XenForo_ControllerPublic_InlineMod_Quotation extends XenForo_ControllerPublic_InlineMod_Abstract
{
	/**
	 * Key for inline mod data.
	 *
	 * @var string
	 */
	public $inlineModKey = 'quotes';
	
	/**
	 * @return XenQuotation_Model_InlineMod_Quote
	 */
	public function getInlineModTypeModel()
	{
		return $this->_getInlineModQuoteModel();
	}
	
	/**
	 * Approves the specified threads.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionApprove()
	{
		return $this->executeInlineModAction('approveQuotes');
	}

	/**
	 * Unapproves the specified threads.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionUnapprove()
	{
		return $this->executeInlineModAction('unapproveQuotes');
	}
	
	/**
	 * Undeletes the specified threads.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionUndelete()
	{
		return $this->executeInlineModAction('undeleteQuotes');
	}
	
	/**
	 * Deletes a quotation.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionDelete()
	{
		if ($this->isConfirmedPost())
		{
			$quoteIds = $this->getInlineModIds();

			$hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::STRING);
			$options = array(
				'deleteType' => ($hardDelete ? 'hard' : 'soft'),
				'reason' => $this->_input->filterSingle('reason', XenForo_Input::STRING)
			);

			$deleted = $this->_getInlineModQuoteModel()->deleteQuotes(
				$quoteIds, $options, $errorPhraseKey
			);
			if (!$deleted)
			{
				throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
			}

			$this->clearCookie();

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(false, false)
			);
		}
		else
		{
			$quoteIds = $this->getInlineModIds();
			$quoteModel = $this->_getQuoteModel();
			
			$fetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions();
			$quotes = $quoteModel->getQuotesByIds($quoteIds, $fetchOptions);
			
			foreach ($quotes as $quote)
			{
				if (!$quoteModel->canDeleteQuote($quote, 'soft'))
				{
					throw $this->getErrorOrNoPermissionResponseException();
				}
			}

			$redirect = $this->getDynamicRedirect();

			if (!$quoteIds)
			{
				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					$redirect
				);
			}

			$viewParams = array(
				'quoteIds' => $quoteIds,
				'quoteCount' => count($quoteIds),
				'canHardDelete' => $quoteModel->canDeleteQuotes('hard'),
				'redirect' => $redirect,
			);

			return $this->responseView('XenQuotation_ViewPublic_InlineMod_Quote_Delete', 'xenquote_inline_mod_quote_delete', $viewParams);
		}
	}
	
	public function _getInlineModQuoteModel()
	{
		return $this->getModelFromCache('XenQuotation_Model_InlineMod_Quote');
	}
	
	public function _getQuoteModel()
	{
		return $this->getModelFromCache('XenQuotation_Model_Quote');
	}
}

?>