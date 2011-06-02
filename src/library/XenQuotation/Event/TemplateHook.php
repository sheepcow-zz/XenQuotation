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
			$options = XenForo_Application::get('options');
			$quoteModel = XenForo_Model::create('XenQuotation_Model_Quote');
			
			$fetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions();
			$quote = $quoteModel->getRandomQuotation($fetchOptions);
			
			if ($quote)
			{
				// add the random quote to the sidebar
				$quoteModel->prepareQuotation($quote);
				$quoteModel->quotationViewed($quote);
				
				$htmlContent = $template->create('xenquote_sidebar_random_quote', 
					array(
						'title' => ($options->xenquoteRandomTitle == '') ? new XenForo_Phrase('xenquote_random_quotation') : $options->xenquoteRandomTitle,
						'quote' => $quote
				))->render();
					
				// work out where to place the quote on the sidebar
				
				if ($options->xenquoteRandomPosition === 'top')
				{
					$contents = $htmlContent . $contents;
				}
				else if ($options->xenquoteRandomPosition === 'bottom')
				{
					$contents .= $htmlContent;
				}
				else if ($options->xenquoteRandomPosition === 'above_members')
				{
					$placeAbove = '<!-- block: sidebar_online_users -->';
					$contents = str_replace($placeAbove, $htmlContent . $placeAbove, $contents);
				}
				else if ($options->xenquoteRandomPosition === 'below_members')
				{
					$placeBelow = '<!-- end block: sidebar_online_users -->';
					$contents = str_replace($placeBelow, $placeBelow . $htmlContent, $contents);
				}
				else if ($options->xenquoteRandomPosition === 'above_stats')
				{
					$placeAbove = '<!-- block: forum_stats -->';
					$contents = str_replace($placeAbove, $htmlContent . $placeAbove, $contents);
				}
				else if ($options->xenquoteRandomPosition === 'below_stats')
				{
					$placeBelow = '<!-- end block: forum_stats -->';
					$contents = str_replace($placeBelow, $placeBelow . $htmlContent, $contents);
				}
				
			}
		}
		else if ($hookName == 'post_public_controls' &&
				 XenForo_Application::get('options')->xenquoteShowSaveAsQuoteLink)
		{
			$postId = false;
			
			if (!empty($hookParams['post']))
			{
				// this will work in 1.0.3
				$postId = $hookParams['post']['post_id'];
			}
			else
			{
				// this will work in 1.0.2
				if (preg_match('#data-posturl=".*posts/([0-9]+)/quote"#iU', $contents, $match))
				{
					$postId = $match[1];
				}				
			}
			
			if ($postId)
			{
				$params = array(
					'canSaveQuotation' => XenForo_Model::create('XenQuotation_Model_Quote')->canAddQuotation(),
					'post' => array('post_id' => $postId)
				);

				$contents .= $template->create('xenquote_post_save_quotation', $params)->render();
			}

		}
	}
}

?>