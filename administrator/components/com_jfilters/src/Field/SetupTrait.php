<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;

\defined('_JEXEC') or die();

/**
 * Trait SetupTrait
 * Used to provide additional functionality to the fields setup
 *
 */
trait SetupTrait
{
    /**
     * We override that function to provide custom attributes to the fields
     *
     * @param \SimpleXMLElement $element
     * @param mixed $value
     * @param null $group
     * @return bool
     * @since 1.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $created = parent::setup($element, $value, $group);

        $attributeNameEdition = 'data-edition';
        if ($created && $element[$attributeNameEdition]) {
            $this->__set($attributeNameEdition, $element[$attributeNameEdition]);
        }

        $attributeNameIncludeTreeFilters = 'data-includeTreeFilters';
        if ($created && $element[$attributeNameIncludeTreeFilters]) {
            $this->__set($attributeNameIncludeTreeFilters, $element[$attributeNameIncludeTreeFilters]);
        }

        $attributeNameDynamicallyAddedOptionsEdition = 'data-dynamicallyAddedOptionsEdition';
        if ($created && $element[$attributeNameDynamicallyAddedOptionsEdition]) {
            $this->__set($attributeNameDynamicallyAddedOptionsEdition, $element[$attributeNameDynamicallyAddedOptionsEdition]);
        }

        /** @var ComponentConfig $componentConfig */
        $componentConfig = ObjectManager::getInstance()->getObject(ComponentConfig::class);

        if ($this->__get('data-edition') && !$componentConfig->get('isPro') && (int)$this->__get('data-edition') == 100) {
            $this->__set('data-locked', true);
            $this->disabled = true;
        }
        return $created;
    }
}