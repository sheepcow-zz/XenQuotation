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
 */
class XenQuotation_ViewPublic_Quote_LikeConfirmed extends XenForo_ViewPublic_Base
{
	/**
	 */
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