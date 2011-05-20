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
 * Catches specific templates and alters the content.
 * Typically used for adding tabs to the search page.
 */
class XenQuotation_Event_TemplateHook
{
	/**
	 * Adds the Quotations tab to the search form.
	 */
	public static function listen($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{		
		if ($hookName == 'search_form_tabs')
		{
			$viewParams = array(
				'searchType' => $template->getParam('searchType')
			);
			
			// append the xenquote_search_form_tabs template
			$contents .= $template->create('xenquote_search_form_tabs', $viewParams)->render();
		}
		else if ($hookName == 'forum_list_sidebar' &&
				 XenForo_Application::get('options')->xenquoteRandomQuote)
		{
			
			$quoteModel = XenForo_Model::create('XenQuotation_Model_Quote');
			
			$fetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions();
			$quote = $quoteModel->getRandomQuotation($fetchOptions);
			
			if ($quote)
			{
				// add the random quote to the sidebar
				$quoteModel->prepareQuotation($quote);
				$quoteModel->quotationViewed($quote);
				
				$htmlContent = $template->create('xenquote_sidebar_random_quote', array('quote' => $quote))->render();
				
				$placeAbove = '<!-- block: forum_stats -->';
				
				$contents = str_replace($placeAbove, $htmlContent . $placeAbove, $contents);
				
			}
		}
	}
}

?>