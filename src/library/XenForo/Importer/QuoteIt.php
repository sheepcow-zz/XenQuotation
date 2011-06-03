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
 * Allows the importing of QuoteIt! vBulletin add-on.
 */
class XenForo_Importer_QuoteIt extends XenForo_Importer_Abstract
{
	protected $_config;
	
	protected $_sourceDb;
	
	protected $_prefix;
	
	protected $_quotesPerStep = 100;
	
	protected $_usersMap;
	
	public static function getName()
	{
		return 'QuoteIt!';
	}
	
	public function configure(XenForo_ControllerAdmin_Abstract $controller, array &$config)
	{
		if ($config)
		{
			$errors = $this->validateConfiguration($config);
			if ($errors)
			{
				return $controller->responseError($errors);
			}

			return true;
		}
		else
		{
			return $controller->responseView('XenQuotation_ViewAdmin_Import_QuoteIt_Config', 'xenquote_import_quoteit_config');
		}
	}
	
	protected function validateConfiguration(array $config)
	{
		$errors = array();
		
		$config['db']['prefix'] = preg_replace('/[^a-z0-9_]/i', '', $config['db']['prefix']);

		try
		{
			$db = Zend_Db::factory('mysqli',
				array(
					'host' => $config['db']['host'],
					'port' => $config['db']['port'],
					'username' => $config['db']['username'],
					'password' => $config['db']['password'],
					'dbname' => $config['db']['dbname'],
					'charset' => $config['db']['charset']
				)
			);
			$db->getConnection();
		}
		catch (Zend_Db_Exception $e)
		{
			$errors[] = new XenForo_Phrase('source_database_connection_details_not_correct_x', array('error' => $e->getMessage()));
		}

		if ($errors)
		{
			return $errors;
		}

		try
		{
			$db->query('
				SELECT quoteid
				FROM ' . $config['db']['prefix'] . 'quotes
				LIMIT 1
			');
		}
		catch (Zend_Db_Exception $e)
		{
			if ($config['db']['dbname'] === '')
			{
				$errors[] = new XenForo_Phrase('please_enter_database_name');
			}
			else
			{
				$errors[] = new XenForo_Phrase('xenquote_table_or_database_name_is_not_correct');
			}
		}

		return $errors;
	}
	
	protected function _bootstrap(array $config)
	{
		if ($this->_sourceDb)
		{
			// already run
			return;
		}

		@set_time_limit(0);

		$this->_config = $config;

		$this->_sourceDb = Zend_Db::factory('mysqli',
			array(
				'host' => $config['db']['host'],
				'port' => $config['db']['port'],
				'username' => $config['db']['username'],
				'password' => $config['db']['password'],
				'dbname' => $config['db']['dbname'],
				'charset' => $config['db']['charset']
			)
		);
		
		$this->_prefix = $config['db']['prefix'];
	}
	
	public function getSteps()
	{
		return array('quotations' => array(
			'title' => 'Quotations'
		));
	}
	
	public function stepQuotations($start, array $options)
	{
		$options = array_merge(array(
			'limit' => $this->_quotesPerStep,
			'max' => false
		), $options);
		
		$sDb = $this->_sourceDb;
		$prefix = $this->_prefix;

		/* @var $model XenForo_Model_Import */
		$model = $this->_importModel;

		if ($options['max'] === false)
		{
			$options['max'] = $sDb->fetchOne('
				SELECT MAX(quoteid)
				FROM ' . $prefix . 'quotes
			');
		}
		
		$quotes = $sDb->fetchAll(
			$sDb->limit(
				'SELECT * FROM ' . $prefix . 'quotes
				 WHERE quoteid > ' . $sDb->quote($start), 
				$options['limit'])
		);
		
		if (!$quotes)
		{
			return true;
		}

		XenForo_Db::beginTransaction();
		
		$next = 0;
		$total = 0;
		foreach ($quotes AS $quote)
		{
			$next = $quote['quoteid'];

			$imported = $this->_importQuote($quote, $options);
			if ($imported)
			{
				$total++;
			}
		}
		
		XenForo_Db::commit();
		
		$this->_session->incrementStepImportTotal($total);
		
		return array($next, $options, $this->_getProgressOutput($next, $options['max']));
	}
	
	protected function _importQuote(array $quote, array $options)
	{		
		$im = $this->_importModel;
		
		if ($this->_usersMap === null)
		{
			$this->_usersMap = $this->_createUserMap();
		}
		
		$import = array(
			'author_user_id' => $this->_mapLookUp($this->_usersMap, $quote['userid'], 0),
			'author_username' => 'Unknown',
			'quote_date' => $quote['date'],
			'quotation' => $this->_convertToUtf8($quote['quote']),
			'quote_state' => ($quote['approved'] == 1) ? 'visible' : 'moderated',
			'attributed_date' => 0,
			'attributed_context' => $this->_convertToUtf8($quote['context']),
			'attributed_post_id' => 0,
			'attributed_user_id' => 0,
			'attributed_username' => '',
			'views' => $quote['views'],
			'likes' => 0,
			'like_users' => serialize(array()),
		);
		
		if (!empty($quote['author']))
		{
			$attribUserId = $im->getUserIdByUserName($quote['author']);
			if ($attribUserId)
			{
				$import['attributed_user_id'] = $attribUserId;
				$import['attributed_username'] = $quote['author'];
			}
			else
			{
				$name = $this->_convertToUtf8($quote['author']);
				
				if (strlen($name) > 50)
				{
					$name = substr($name, 0, 47).'...';
				}
				
				$import['attributed_username'] = $name;
			}	
		}
		
		if ($import['author_user_id'])
		{
			$user = XenForo_DataWriter::create('XenForo_Model_User')->getUserById($import['author_user_id']);
			
			if ($user)
			{
				$import['author_username'] = $user['username'];
			}
			else
			{
				$import['author_username'] = 'Unknown';
				$import['author_user_id'] = 0;
			}
		}
		
		$importedQuoteId = $this->_importQuotation($quote['quoteid'], $import, $failedKey);
		
		if ($importedQuoteId)
		{
		}
		else if ($failedKey)
		{
			$this->_session->setExtraData('quoteFailed', $quote['quoteid'], $failedKey);
		}
		
		return $importedQuoteId;
	}
	
	protected function _importQuotation($oldId, array $info, &$failedKey = '')
	{
		$im = $this->_importModel;
				
		XenForo_Db::beginTransaction();

		$dw = XenForo_DataWriter::create('XenQuotation_DataWriter_Quote');
		$dw->setImportMode(true);
		$dw->bulkSet($info);
		if ($dw->save())
		{
			$newId = $dw->get('quote_id');
			if ($oldId !== 0 && $oldId !== '')
			{
				$im->logImportData('quote', $oldId, $newId);
			}
		}
		else
		{
			$newId = false;
		}

		XenForo_Db::commit();

		return $newId;
	}
	
	protected function _createUserMap()
	{
		$map = array();
		
		$sDb = $this->_sourceDb;
		$prefix = $this->_prefix;
		
		try
		{
			$data = $sDb->fetchAll(
				'SELECT users.`userid`, users.`username` FROM ' . $prefix . 'users AS users'
			);
		}
		catch (Zend_Db_Exception $e)
		{
			$data = false;
		}
		
		if ($data)
		{
			foreach ($data as $user)
			{
				$userId = $im->getUserIdByUserName($user['username']);
				if ($userId)
				{
					$map[$user['userid']] = $userId;
				}
			}
		}
		
		return $map;
	}
}

?>