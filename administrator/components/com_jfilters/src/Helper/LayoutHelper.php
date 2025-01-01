<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Helper;

\defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

class LayoutHelper
{
    /**
     * Set a modal in a page.
     *
     * @param   string  $id
     * @param   string  $title
     * @param   string  $url
     * @param   bool    $hasButtons
     *
     * @since 1.0.0
     */
    public static function setModal($id, $title, $url, $hasButtons = true)
    {
        $applyBtnSelector = '#toolbar-apply button';

        // Joomla 4 use different toolbar buttons, than J5
        if (version_compare(Version::MAJOR_VERSION, '5', '<')) {
            $applyBtnSelector = '#applyBtn';
        }

        echo HTMLHelper::_(
            'bootstrap.renderModal',
            $id,
            [
                'url'         => $url,
                'title'       => $title,
                'height'      => '400px',
                'width'       => '800px',
                'bodyHeight'  => '70',
                'modalWidth'  => '80',
                'closeButton' => false,
                'backdrop'    => 'static',
                'keyboard'    => false,
                'footer'      => $hasButtons ? '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"'
                    . ' onclick="window.parent.Joomla.Modal.getCurrent().close();">'
                    . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                    . '<button type="button" class="btn btn-success" onclick="Joomla.iframeButtonClick({iframeSelector: \'#' . $id . '\', buttonSelector: \'' . $applyBtnSelector . '\'}); return false;">'
                    . Text::_("JAPPLY") . '</button>' : ''
            ]
        );

        // Reload the parent window, after the modal is closed.
        Factory::getDocument()->getWebAssetManager()->addInlineScript(
            <<<JS
document.addEventListener('DOMContentLoaded', function() {
	document.querySelector('#{$id}').addEventListener('hide.bs.modal', function() {
	    window.parent.location.reload();
	});
});
JS
        );
    }
}