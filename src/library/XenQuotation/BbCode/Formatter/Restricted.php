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
 * This is a restricted BB code formatter. It allows specific
 * BB codes to be removed before parsing.
 */
class XenQuotation_BbCode_Formatter_Restricted extends XenForo_BbCode_Formatter_Base
{
	protected $_tags = null;
	
	/**
	 * Gets the BB code tags, removes any tags that 
	 * have been restricted.
	 *
	 * @return array
	 */
	public function getTags()
	{
		if (!is_array($this->_tags))
		{
			// get all the tags available
			$tags = parent::getTags();
				
			$this->_tags = array();
			$options = XenForo_Application::get('options');
			
			if ($options->xenquoteAllowedBbCode)
			{
				/*
				 * Remove all tags except the ones that
				 * have been explicitly allowed
				 */
				$allowedTags = $options->xenquoteAllowedBbCode;

				foreach ($tags as $tag => $parseInfo)
				{
					if (isset($allowedTags['tag_' . $tag]))
					{
						$this->_tags[$tag] = $parseInfo;
					}
				}
			}
		}

		return $this->_tags;
	}
}

?>