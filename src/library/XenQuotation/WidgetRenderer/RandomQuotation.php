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
 * Renderer for the Random Quotation widget.
 */
class XenQuotation_WidgetRenderer_RandomQuotation extends WidgetFramework_WidgetRenderer
{
	protected function _getConfiguration() {
		return array('name' => new XenForo_Phrase('xenquote_random_widget_name'));
	}
	
	protected function _getOptionsTemplate() {
		return false;
	}
	
	protected function _getRenderTemplate($templateName, array $params) {
		return 'xenquote_sidebar_random_quote_content';
	}
	
	protected function _render(array $widget, $templateName, array $params, XenForo_Template_Abstract $renderTemplateObject) {
		
		$quoteModel = XenForo_Model::create('XenQuotation_Model_Quote');
		
		$fetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions();
		$quote = $quoteModel->getRandomQuotation($fetchOptions);
		
		if ($quote)
		{
			// add the random quote to the sidebar
			$quoteModel->prepareQuotation($quote);
			$quoteModel->quotationViewed($quote);
		}
		
		$renderTemplateObject->setParam('quote', $quote);

		return $renderTemplateObject->render();
	}
}

?>