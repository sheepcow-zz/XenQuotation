<?php

/**
 * Helper for quotation related pages.
 * Provides validation methods, amongst other things.
 */
class XenQuotation_ControllerHelper_Quote extends XenForo_ControllerHelper_Abstract
{
	/**
	 * The current browsing user.
	 *
	 * @var XenForo_Visitor
	 */
	protected $_visitor;

	/**
	 * Additional constructor setup behavior.
	 */
	protected function _constructSetup()
	{
		$this->_visitor = XenForo_Visitor::getInstance();
	}
	
	/**
	 */
	public function assertQuoteValidAndViewable($quoteId)
	{
		$quote = $this->getQuoteOrError($quoteId);
		
		$quoteModel = $this->_controller->getModelFromCache('XenQuotation_Model_Quote');
		
		if (!$quoteModel->canViewQuotation($quoteId, $errorPhraseKey))
		{
			throw $this->_controller->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}
		
		return $quote;
	}
	
	/**
	 */
	public function getQuoteOrError($quoteId, array $fetchOptions = array())
	{
		$quote = $this->_controller->getModelFromCache('XenQuotation_Model_Quote')->getQuoteById($quoteId, $fetchOptions);
		if (!$quote)
		{
			throw $this->_controller->responseException(
				$this->_controller->responseError(new XenForo_Phrase('xenquote_requested_quotation_not_found'), 404)
			);
		}

		return $quote;
	}
}

?>