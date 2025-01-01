<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextCollection;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Language\Text;

class ContextaliastextField extends TextField
{
    /**
     * Override this to display the context in a more meaningful way
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
        $result = false;

        /** @var  ContextCollection $contextCollection */
        $contextCollection = ObjectManager::getInstance()->getObject(ContextCollection::class);
        $context = $this->form->getValue('context');
        if($context) {
            $contextItem = $contextCollection->getByNameAttribute($context);
            if ($contextItem) {
                $value .= $contextItem->getAlias() ? Text::_($contextItem->getAlias()) : $context;
            }

            $result = parent::setup($element, $value, $group);
        }
        return $result;
    }
}