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
class XenQuotation_ModerationQueueHandler_Quote extends XenForo_ModerationQueueHandler_Abstract
{
	/**
	 * Gets visible moderation queue entries for specified user.
	 *
	 * @see XenForo_ModerationQueueHandler_Abstract::getVisibleModerationQueueEntriesForUser()
	 */
	public function getVisibleModerationQueueEntriesForUser(array $contentIds, array $viewingUser)
	{
		/* @var $quoteModel XenQuotation_Model_Quote */
		$quoteModel = XenForo_Model::create('XenQuotation_Model_Quote');
		$fetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions();
		$quotes = $quoteModel->getQuotesByIds($contentIds, $fetchOptions);

		$output = array();
		foreach ($quotes AS $quote)
		{
			$canManage = true;
			if (!$quoteModel->canViewQuotation(
				$quote['quote_id'], $null, $viewingUser
			))
			{
				$canManage = false;
			}
			else if (!$quoteModel->canEditAny()
				|| !$quoteModel->canDeleteAny()
			)
			{
				$canManage = false;
			}

			if ($canManage)
			{
				$output[$quote['quote_id']] = array(
					'message' => $quote['quotation'],
					'user' => array(
						'user_id' => $quote['author_user_id'],
						'username' => $quote['author_username']
					),
					'title' => new XenForo_Phrase('xenquote_quotation_x', array('id' => $quote['quote_id'])),
					'link' => XenForo_Link::buildPublicLink('quotes', $quote),
					'contentTypeTitle' => new XenForo_Phrase('xenquote_quotation'),
					'titleEdit' => false
				);
			}
		}

		return $output;
	}
	
	/**
	 * Approves the specified moderation queue entry.
	 *
	 * @see XenForo_ModerationQueueHandler_Abstract::approveModerationQueueEntry()
	 */
	public function approveModerationQueueEntry($contentId, $message, $title)
	{
		$dw = XenForo_DataWriter::create('XenQuotation_DataWriter_Quote', XenForo_DataWriter::ERROR_SILENT);
		$dw->setExistingData($contentId);
		$dw->set('quote_state', 'visible');
		$dw->set('quotation', $message);

		return $dw->save();
	}

	/**
	 * Deletes the specified moderation queue entry.
	 *
	 * @see XenForo_ModerationQueueHandler_Abstract::deleteModerationQueueEntry()
	 */
	public function deleteModerationQueueEntry($contentId)
	{
		$dw = XenForo_DataWriter::create('XenQuotation_DataWriter_Quote', XenForo_DataWriter::ERROR_SILENT);
		$dw->setExistingData($contentId);
		$dw->set('quote_state', 'deleted');

		return $dw->save();
	}
}

?>