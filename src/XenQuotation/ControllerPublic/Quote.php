<?php

/**
 * Controller for the quotes.
 */
class XenQuotation_ControllerPublic_Quote extends XenForo_ControllerPublic_Abstract
{
	
	/**
	 * Reroutes to either display a list of all the quotes or, if 
	 * a quote id has been supplied, view a specific quote.
	 */
	public function actionIndex()
	{
		if ($this->_input->inRequest('quote_id'))
		{
			// a quote id has been supplied, redirect to view the quote
			return $this->responseReroute(__CLASS__, 'view');
		}
		
		// otherwise, redirect to the quotes list
		return $this->responseReroute(__CLASS__, 'list');
	}
	
	/**
	 * Displays the basic list of quotes to the user.
	 */
	public function actionList()
	{
		$viewParams = array(
			'page' => 1,
			'canCreateQuotation' => true
		);
		
		return $this->responseView('XenQuotation_ViewPublic_Quote_List', 'xenquote_quote_list', $viewParams);
	}

	/**
	 * Viewing a specific quote
	 */	
	public function actionView()
	{
		$viewParams = array();
		return $this->responseView('XenQuotation_ViewPublic_Quote_List', 'DEFAULT', $viewParams);	
	}
	
	/**
	 * Displays a form to allow users to edit quotes.
	 */
	public function actionEdit()
	{
		$viewParams = array();
		return $this->responseView('XenQuotation_ViewPublic_Quote_List', 'DEFAULT', $viewParams);
	}
	
	/**
	 * Shows a preview of the edit.
	 */
	public function actionEditPreview()
	{
	}
	
	/**
	 * Creates a new post or saves the changes to an existing one.
	 */
	public function actionSave()
	{
		$this->_assertPostOnly();
	}
	
	/**
	 * Create a new quote
	 */
	public function actionCreateQuote()
	{
		$this->_assertCanAddQuotation();
		
		$viewParams = array();
		return $this->responseView('XenQuotation_ViewPublic_Quote_Create', 'xenquote_quote_create', $viewParams);
	}
	
	/**
	 *
	 */
	public function actionLike()
	{
	}
	
	/**
	 * Lists everyone that likes a particular quote.
	 */
	public function actionLikes()
	{
	}
	
	/**
	 * Session activity details.
	 * @see XenForo_Controller::getSessionActivityDetailsForList()
	 */
	public static function getSessionActivityDetailsForList(array $activities)
	{
		return new XenForo_Phrase('xenquote_viewing_a_quote');
	}
	
	protected function _assertCanAddQuotation()
	{		
		if (!$this->_getQuoteModel()->canAddQuotation($errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
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