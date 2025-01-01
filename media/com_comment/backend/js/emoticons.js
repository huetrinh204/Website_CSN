/**
 * @author Daniel Dimitrov - compojoom.com
 * @date: 22.08.2021
 *
 * @copyright  Copyright (C) 2008 - 2021 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
var ccommentEmoticons;

(function($) {
	var showEmoticons = function (el) {
		var selected = el.val();
		$('.emoticons').css('display', 'none');

		$('#emoticons-' + selected).css('display', 'block');
	}

	ccommentEmoticons = function (el) {
		var el = $(el)

		el.on('change', function () {
			showEmoticons(el)
		})

		showEmoticons(el)
	}
})(jQuery)
