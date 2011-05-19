<?php

class XenQuotation_ViewPublic_Quote_LikeConfirmed extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$message = $this->_params['quote'];

		if (!empty($message['likeUsers']))
		{
			$params = array(
				'message' => $message,
				'likesUrl' => XenForo_Link::buildPublicLink('quotes/likes', $message)
			);

			//$output = $this->_renderer->getDefaultOutputArray(get_class($this), $params, 'likes_summary');
			$output = $this->_renderer->getDefaultOutputArray(get_class($this), $params, '');
		}
		else
		{
			$output = array('templateHtml' => '', 'js' => '', 'css' => '');
		}
		
		if ($this->_params['liked'])
		{
			$output['term'] = new XenForo_Phrase('unlike');

			$output['cssClasses'] = array(
				'like' => '-',
				'unlike' => '+'
			);
		}
		else
		{
			$output['term'] = new XenForo_Phrase('like');

			$output['cssClasses'] = array(
				'like' => '+',
				'unlike' => '-'
			);
		}

		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}

}