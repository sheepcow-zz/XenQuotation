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
 * Custom renderer for the allowed BB code option.
 */
class XenQuotation_Option_AllowedBbCode
{
	/**
	 * @param XenForo_View $view View object
	 * @param string $fieldPrefix Prefix for the HTML form field name
	 * @param array $preparedOption Prepared option info
	 * @param boolean $canEdit True if an "edit" link should appear
	 *
	 * @return XenForo_Template_Abstract Template object
	 */
	public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$preparedOption['edit_format'] = 'checkbox';
		$preparedOption['formatParams'] = XenQuotation_Option_AllowedBbCode::getBbCodeForOptionsTag();

		return XenForo_ViewAdmin_Helper_Option::renderPreparedOptionHtml(
			$view, $preparedOption, $canEdit, $fieldPrefix
		);
	}
	
	protected static function getBbCodeForOptionsTag()
	{
		$options = array();
		
		$formatter = new XenForo_BbCode_Formatter_Base();
		$tags = $formatter->getTags();
		
		foreach ($tags as $tag => $value)
		{
			$options['tag_' . $tag] = new XenForo_Phrase('xenquote_option_allowedbbcode_tag', array('tag' => $tag));
		}
		
		return $options;
	}
}

?>