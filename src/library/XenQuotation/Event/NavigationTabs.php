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
 * Listens for the navigation_tabs event and adds the
 * quotes tab to the list.
 */
class XenQuotation_Event_NavigationTabs
{
	/**
	 * Callback that adds the quotes tab to the navigation tabs.
	 *
	 * @param array $extraTabs
	 * @param string $selectedTabId
	 */
	public static function listen(array &$extraTabs, $selectedTabId)
	{
		// get the visitor instance
		$visitor = XenForo_Visitor::getInstance();
		
		// get the quote model
		$quoteModel = new XenQuotation_Model_Quote();
		
		$extraTabs['quotes'] = array(
			'title' => new XenForo_Phrase('xenquote_navtab_quotes'),
			'href' => XenForo_Link::buildPublicLink('quotes'),
			'linksTemplate' => 'xenquote_navigation_tab',
			'position' => 'middle',
			
			/* 
			 * Inject any parameters that we want to access
			 * the navigation tab template.
			 */
			
			'canSearch' => $visitor->canSearch(),
			'canAddQuote' => $quoteModel->canAddQuotation()
		);
	}
}

?>