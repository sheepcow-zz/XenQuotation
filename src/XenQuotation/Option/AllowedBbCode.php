<?php

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