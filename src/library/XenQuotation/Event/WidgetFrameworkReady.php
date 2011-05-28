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
 * Catches the widget framework ready code event and
 * adds the Random Quotation widget to the framework.
 */
class XenQuotation_Event_WidgetFrameworkReady
{
	/**
	 * Adds the random quotation widget to the framework.
	 */
	public static function listen(array &$renderers)
	{
		$renderers[] = 'XenQuotation_WidgetRenderer_RandomQuotation';
	}
}


?>