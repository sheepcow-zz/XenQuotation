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
 * EWRporta random quotation block module.
 */
class EWRporta_Block_RandomQuotation extends XenForo_Model
{	
	public function getBypass()
	{		
		$quoteModel = $this->getModelFromCache('XenQuotation_Model_Quote');
		
		$fetchOptions = $quoteModel->getPermissionBasedQuoteFetchOptions();
		$quote = $quoteModel->getRandomQuotation($fetchOptions);
		
		if ($quote)
		{
			// add the random quote to the sidebar
			$quoteModel->prepareQuotation($quote);
			$quoteModel->quotationViewed($quote);
		}

		return $quote;
	}
}

?>