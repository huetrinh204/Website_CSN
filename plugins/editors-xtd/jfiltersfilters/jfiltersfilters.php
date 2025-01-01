<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;

/**
 * Editor JFilters button
 * @since 1.4.0
 */
class plgButtonJfiltersfilters extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  1.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * @param $name
     *
     * @return CMSObject|void
     * @throws Exception
     * @since 1.4.0
     */
    public function onDisplay($name)
    {
        $user  = Factory::getApplication()->getIdentity();

        // Check if com_jfilters is enabled or the user has the proper permissions.
        if (!ComponentHelper::isEnabled('com_jfilters') ||
            (
                !$user->authorise('core.edit', 'com_jfilters')
                && !$user->authorise('core.edit.own', 'com_jfilters')
            )) {
            return;
        }

        // Guess the field context based on view.
        $jinput = Factory::getApplication()->input;
        $context = $jinput->get('option') . '.' . $jinput->get('view');

        $link = 'index.php?option=com_jfilters&amp;view=filters&amp;layout=modal&amp;tmpl=component&amp;context='
            . $context . '&amp;editor=' . $name . '&amp;client=editor&amp;' . Session::getFormToken() . '=1';

        $button = new CMSObject;
        $button->modal = true;
        $button->link = $link;
        $button->text = Text::_('PLG_EDITORS-XTD_JFILTERSFILTERS_LABEL');
        $button->name = $this->_type . '_' . $this->_name;
        $button->icon = 'filter';
        $button->iconSVG = '<svg version="1.1" viewBox="0 0 16.791 16" xmlns="http://www.w3.org/2000/svg">
    <g transform="matrix(.23179 0 0 .23179 -64.802 -41.713)">
    <path transform="translate(0,2.428)" d="m280.99 189.48 1.411-1.439v2.823z" fill="#038be0"/>
    <path transform="translate(2.761)" d="m288.14 182.03h-0.2l0.1-0.1z"/>
    <g transform="translate(282.4 182.03)">
    <path transform="translate(-282,-182)" d="m326.31 217.23v30.448h-13.973v-24.593l-27.583-27.108-2.753-2.711v-2.822l8.3-8.44h0.2l14.211 13.973z" fill="#038be0"/>
    <path transform="translate(-279.58 -182)" d="m347.2 193.93-21.463 21.393-9.865-9.907 9.488-9.446h-23.07l-14.21-13.973h54.188z" fill="#81ddff"/>
    </g></g></svg>';
        $button->options = [
            'height' => '300px',
            'width' => '800px',
            'bodyHeight' => '70',
            'modalWidth' => '80',
            'confirmCallback' => 'applyFilters(\'editor\');',
            'confirmText'     => Text::_('PLG_EDITORS-XTD_JFILTERSFILTERS_APPLY_FILTERS'),
        ];

        return $button;
    }
}