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
 * This is the installation class. It is called by the XenForo
 * application when the add-on is upgraded or (un)installed.
 */
class XenQuotation_Installation
{
	
	/**
	 * Creates the SQL tables required by the add-on and registers
	 * the necessary types with the XenForo application.
	 */
	public static function install($existingAddon = false, array $addon)
	{
		// get the database object
		$db = XenForo_Application::get('db');
		
		$db->query(
			"CREATE TABLE IF NOT EXISTS `xq_quotation` (
			  `quote_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `author_user_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `author_username` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `quote_date` int(10) unsigned NOT NULL DEFAULT '0',
			  `quotation` text COLLATE utf8_unicode_ci NOT NULL,
			  `quote_state` enum('visible','moderated','deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'moderated',
			  `attributed_date` int(10) unsigned NOT NULL DEFAULT '0',
			  `attributed_context` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `attributed_post_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `attributed_user_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `attributed_username` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `views` int(10) unsigned NOT NULL DEFAULT '0',
			  `likes` int(10) unsigned NOT NULL DEFAULT '0',
			  `like_users` blob NOT NULL,
			  PRIMARY KEY (`quote_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1"
		);
		
		/*
		 * Set up the quotes content type
		 */
		
		$contentType = array(
			'like_handler_class' => 'XenQuotation_LikeHandler_Quote',
			'search_handler_class' => 'XenQuotation_Search_DataHandler_Quote',
			'alert_handler_class' => 'XenQuotation_AlertHandler_Quote'
		);
	
		/*$db->query(
			"REPLACE INTO `xf_content_type`
			(`content_type`, `addon_id`, `fields`)
			VALUES
			('quote', 'XenQuotation', '" . serialize($contentType) . "')"
		);*/
		
		foreach ($contentType as $name => $value)
		{
			$db->query("INSERT INTO `xf_content_type_field` (`content_type`, `field_name`, `field_value`) 
					    VALUES(?, ?, ?)", array('quote', $name, $value));
		}
	}
	
	/**
	 * Removes the SQL tables used by this add-on.
	 */
	public static function uninstall()
	{
		// get the database object
		$db = XenForo_Application::get('db');
		
		// remove the table from the database
		$db->query("DROP TABLE IF EXISTS xq_quotation");
	}
	
}

?>