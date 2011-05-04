<?php

/**
 * The DataWriter class deals with validating and writing
 * quotes to the database.
 */
class FhTools_Quotation_DataWriter_Quote
{
	protected $_existingDataErrorPhrase = 'fhtq_requested_quote_not_found';
	
	protected function _getFields()
	{
		return array(
			'fht_quotation' => array(
				'quote_id'				=> array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'author_user_id'		=> array('type' => self::TYPE_UINT, 'default' => 0),
				'author_username'		=> array('type' => self::TYPE_STRING, 'default' => ''),
				'quote_date'			=> array('type' => self::TYPE_UINT, 
												 'default' => XenForo_Application::$time),
				'quote'					=> array('type' => self::TYPE_STRING, 'required' => true,
												 'requiredError' => 'fhtq_please_enter_a_quote'),
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
			'fht_quote' => $this->getModelFromCache('FhTools_Quotation_Model_Quote')->getQuoteById(
				$quoteId, array('quoteStates' => array('new', 'approved', 'rejected'))
			)
		);
	}

	protected function _getUpdateCondition($tableName)
	{
		return '`quote_id`=' . $this->_db->quote($this->getExisting('quote_id'));
	}
	
}

?>