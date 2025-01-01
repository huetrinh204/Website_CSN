<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Class ConfigtypetextField
 * @package Bluecoder\Component\Jfilters\Administrator\Field
 */
class ConfigtypetextField extends TextField
{
    /**
     * Override this to display the config type in a more meaningful way
     *
     * @param   \SimpleXMLElement  $element
     * @param   mixed              $value
     * @param   null               $group
     *
     * @return bool
     * @since 1.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $configType = $this->form->getValue('config_name');
        if ($configType) {
            $attributes = new Registry($this->form->getValue('attribs'));
            $value = Text::_(ConfigtypeField::createLabel($configType));
            $value .= $attributes->get('type') ? ' (' . $attributes->get('type') . ')' : '';
        }

        return parent::setup($element, $value, $group);
    }
}
