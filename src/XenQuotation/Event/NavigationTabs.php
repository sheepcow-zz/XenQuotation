<?php

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