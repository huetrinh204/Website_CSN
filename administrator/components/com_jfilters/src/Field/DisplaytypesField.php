<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field\Display;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\DynamicInterface as DynamicConfigFilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilterInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Language\Text;
use ReflectionException;

/**
 * Class DisplaytypesField- used for displaying the various display types
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Field
 */
class DisplaytypesField extends JfilterslistField
{

    /**
     * The default display type
     */
    const DEFAULT_DISPLAY_TYPE = 'links';

    /**
     * The display types
     */
    const DISPLAY_TYPE_LABELS =
        [
            'list' => 'COM_JFILTERS_DISPLAY_DROP_DOWN_LIST',
            'radios' => 'COM_JFILTERS_DISPLAY_RADIO_BUTTONS',
            'checkboxes' => 'COM_JFILTERS_DISPLAY_CHECKBOXES',
            'links' => 'COM_JFILTERS_DISPLAY_LINKS',
            'buttons_single' => 'COM_JFILTERS_DISPLAY_BUTTONS',
            'buttons_multi' => 'COM_JFILTERS_DISPLAY_BUTTONS_MULTI_SELECT',
            'calendar' => 'COM_JFILTERS_DISPLAY_CALENDAR',
            'range_inputs' => 'COM_JFILTERS_DISPLAY_RANGE_INPUTS',
            'range_sliders' => 'COM_JFILTERS_DISPLAY_RANGE_SLIDERS',
            'range_inputs_sliders' => 'COM_JFILTERS_DISPLAY_RANGE_INPUTS_SLIDERS',
        ];

    /**
     * The form field type.
     *
     * @var string
     * @since 1.0.0
     */
    protected $type = 'DisplayTypes';

    /**
     * Get the label of that display type
     *
     * @param $displayType
     *
     * @return string
     * @since 1.0.0
     */
    public static function getDisplayTypeName($displayType): string
    {
        $displayTypeLabel = $displayType;
        if (isset(self::DISPLAY_TYPE_LABELS[$displayType])) {
            $displayTypeLabel = self::DISPLAY_TYPE_LABELS[$displayType];
        }

        return Text::_($displayTypeLabel);
    }

    /**
     * Is the display multi-select or not.
     *
     * @return bool
     * @throws ReflectionException
     * @since 1.0.0
     */
    public function isMultiSelect(): bool
    {
        $multiselect = false;
        $options     = $this->getOptions();
        $filter      = $this->getFilter();
        foreach ($options as $option) {
            if ($filter && $option->value === $filter->getDisplay()) {
                $multiselect = isset($option->multiselect) && $option->multiselect == 'true';
                break;
            }
        }

        return $multiselect;
    }

    /**
     * Is the display a range
     *
     * @return bool
     * @throws ReflectionException
     * @since 1.14.0
     */
    public function isRange(): bool
    {
        $isRange = false;
        $options     = $this->getOptions();
        $filter      = $this->getFilter();
        foreach ($options as $option) {
            if ($filter && $option->value === $filter->getDisplay()) {
                $isRange = isset($option->range) && $option->range == 'true';
                break;
            }
        }

        return $isRange;
    }

    /**
     * Method to get the field options.
     *
     * @return array
     * @throws ReflectionException
     * @since 1.0.0
     */
    public function getOptions(): array
    {
        $newOptions            = [];
        $options               = parent::getOptions();
        $filterAllowedDisplays = $this->getFilterDisplays();

        /*
         * The filter defines its own available display types, through its configuration.
         * Use these instead.
         */
        if ($filterAllowedDisplays) {
            $options = $filterAllowedDisplays;
        }

        foreach ($options as $option) {
            $optionDataTypes = explode(',', $option->dataType ?? '');
            $optionDataTypes = array_filter($optionDataTypes);
            $optionDataTypes = array_map(function ($element) {return trim($element);}, $optionDataTypes);

            if (!$optionDataTypes || in_array($this->getFilter()->getAttributes()->get('dataType'), $optionDataTypes)) {
                // when empty is either a "select smth" or "use global"
                if ($option->value != '') {
                    $option->text = self::getDisplayTypeName($option->value);
                }
                $newOptions[] = $option;
            }
        }

        $this->adjustProOptions($newOptions);
        return $newOptions;
    }

    /**
     * Get the available display types for that filter, from its config file, if exists
     *
     * @return array
     * @throws ReflectionException
     * @since 1.0.0
     */
    protected function getFilterDisplays(): array
    {
        $displays   = [];
        $filterType = $this->getFilterType();

        // There is $filterType only for dynamic filters.
        if ($filterType) {
            /** @var DynamicConfigCollection $dynamicConfigCollection */
            $dynamicConfigCollection = ObjectManager::getInstance()->getObject(DynamicConfigCollection::class);
            /** @var DynamicConfigFilterInterface $dynamicConfigFilter */
            $dynamicConfigFilter = $dynamicConfigCollection->getByNameAttribute($filterType);
            if ($dynamicConfigFilter) {
                $displayArray = $dynamicConfigFilter->getDisplays();

                if ($displayArray) {
                    /** @var Display $display */
                    foreach ($displayArray as $display) {
                        $displaObj = new \stdClass();
                        $displaObj->value = $display->getValue();
                        $displaObj->text = $display->getName();
                        $displaObj->edition = $display->getEdition();
                        $displaObj->multiselect = $display->getMultiselect();
                        $displaObj->isRange = $display->getRange();
                        $displays[] = $displaObj;
                    }
                }
            }
        }

        return $displays;
    }

    /**
     * Get the type of the dynamic filter
     *
     * @return string
     * @throws ReflectionException
     * @since 1.0.0
     */
    protected function getFilterType(): string
    {
        $filterType = '';
        $filter     = $this->getFilter();

        // get the display from the configuration for the dynamic filters, if exist.
        if ($filter && $filter instanceof DynamicFilterInterface) {
            /** @var DynamicFilterInterface $filter */
            $filterType = $filter->getType();
        }

        return $filterType ?? '';
    }
}
