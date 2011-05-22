
// create the XenQuotation namespace
var XenQuotation = {};

// Handle like/unlike links
XenForo.register('a.LikeCounter', 'XenQuotation.LikeCounter');

/**
 * Handles a like / unlike link being clicked 
 * and a like counter needs to be updated.
 *
 * @param jQuery a.LikeCounter
 */
XenQuotation.LikeCounter = function($link)
{
	$link.click(function(e)
	{
		e.preventDefault();

		var $link = $(this);

		XenForo.ajax(this.href, {}, function(ajaxData, textStatus)
		{
			if (XenForo.hasResponseError(ajaxData))
			{
				return false;
			}

			$link.stop(true, true);

			if (ajaxData.term) // term = Like / Unlike
			{
				$link.find('.LikeLabel').html(ajaxData.term);

				if (ajaxData.cssClasses)
				{
					$.each(ajaxData.cssClasses, function(className, action)
					{
						$link[action == '+' ? 'addClass' : 'removeClass'](className);
					});
				}
			}

			if (ajaxData.templateHtml === '')
			{
				$($link.data('container')).xfFadeUp(XenForo.speed.fast, function()
				{
					$(this).empty().xfFadeDown(0);
				});
			}
			else
			{
				var $container    = $($link.data('container')),
					$likeText     = $container.find('.LikeText'),
					$templateHtml = $(ajaxData.templateHtml);

				new XenForo.ExtLoader(ajaxData, function()
				{
					$templateHtml.xfInsert('replaceAll', $likeText);
				});
			}
		});
	});
};