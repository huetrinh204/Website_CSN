<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilter\FieldsFilter;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Class FieldsOption
 *
 * The class is used for Option objects generated from the Fields (i.e. Custom Fields)
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option
 */
class Field extends Option
{
    /**
     * @var DynamicConfigCollection
     */
    protected $dynamicConfigCollection;

    /**
     * Field constructor.
     *
     * @param   UriHandlerInterface      $uri
     * @param   ComponentConfig          $componentConfig
     * @param   DynamicConfigCollection  $dynamicConfigCollection
     * @param   array                    $properties
     */
    public function __construct(UriHandlerInterface $uri, ComponentConfig $componentConfig, DynamicConfigCollection $dynamicConfigCollection, array $properties = [])
    {
        $this->dynamicConfigCollection = $dynamicConfigCollection;
        parent::__construct($uri, $componentConfig, $properties);
    }

    /**
     * Get the label from the field's params.
     * A field could have different value and label.
     *
     * The fields save the labels in their params.
     *
     * @return string
     */
    public function getLabel(): string
    {
        if ($this->labelResolved === false) {
            /** @var FieldsFilter $filter */
            $filter = $this->getParentFilter();
            $this->setLabel(parent::getLabel());

            // Get the label from the params
            if (method_exists($filter, 'getParams')) {
                /** @var Registry $fieldParams */
                $fieldParams = $filter->getParams();
                foreach ($fieldParams->get('options', array()) as $option) {
                    $op = (object)$option;
                    $optionValue = !empty($op->value) || $op->value == '0' ? $op->value : '';
                    // Strip that if we did it to the original value.
                    $optionValue = $optionValue && $this->isValueStripped ? $this->subString($op->value, $this->componentConfig->get('max_option_value_length', 25)) : $optionValue;
                    if ($optionValue == $this->getValue() && isset($op->name)) {
                        $this->setLabel($op->name);
                        break;
                    }
                }
            }
            // Translate but not numbers and dates
            $this->setLabel(in_array($this->getParentFilter()->getAttributes()->get('dataType', ''), ['int', 'date']) ? $this->label : Text::_($this->label));
            $this->labelResolved = true;
        }
        return $this->label;
    }
}
