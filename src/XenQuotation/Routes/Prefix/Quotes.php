<?php

/**
 * Route and link builder for quotes.
 */
class XenQuotation_Routes_Prefix_Quotes implements XenForo_Route_Interface
{
	/**
	 * Match a specific route for an already matched prefix.
	 *
	 * @see XenForo_Route_Interface::match()
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithIntegerParam($routePath, $request, 'quote_id');
		$action = $router->resolveActionAsPageNumber($action, $request);
		return $router->getRouteMatch('XenQuotation_ControllerPublic_Quote', $action, 'quotes');
	}

	/**
	 * Method to build a link to the specified page/action with the provided
	 * data and params.
	 *
	 * @see XenForo_Route_BuilderInterface
	 */
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		$action = XenForo_Link::getPageNumberAsAction($action, $extraParams);
		return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'quote_id');
	}
}

?>