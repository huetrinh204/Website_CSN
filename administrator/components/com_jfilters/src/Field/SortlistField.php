<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\DynamicInterface as DynamicConfigFilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilterInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Language\Text;

/**
 * Class SortlistField
 * Loads the sort fields for the filter options, including those declared per filtering in the dynamic config
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Field
 */
class SortlistField extends JfilterslistField
{
    /**
     * Method to get the field options.
     *
     * @return array
     * @throws \ReflectionException
     * @since 1.3.0
     */
    public function getOptions()
    {
        $newOptions = [];
        $options = parent::getOptions();
        $filterAdditionalSortFields = $this->getFilterOrderingOptions();

        /*
         * The filter defines its own additional sorting options, through its configuration.
         * Merge them with the existing.
         */
        if ($filterAdditionalSortFields) {
            $options = array_merge($options, $filterAdditionalSortFields);
        }

        $this->adjustProOptions($options);
        return $options ;
    }

    /**
     * Get the available ordering options for that filter
     *
     * @return array
     * @throws \ReflectionException
     * @since 1.3.0
     */
    protected function getFilterOrderingOptions(): array
    {
        $sortFields   = [];
        $filterType = $this->getFilterType();
        if ($filterType) {
            /** @var DynamicConfigCollection $dynamicConfigCollection */
            $dynamicConfigCollection = ObjectManager::getInstance()->getObject(DynamicConfigCollection::class);
            /** @var DynamicConfigFilterInterface $dynamicConfigFilter */
            $dynamicConfigFilter = $dynamicConfigCollection->getByNameAttribute($filterType);
            if ($dynamicConfigFilter) {
                $sortFieldsArray = $dynamicConfigFilter->getAdditionalSortFields();

                if ($sortFieldsArray) {
                    /** @var Field $sortField */
                    foreach ($sortFieldsArray as $sortField) {
                        $sortObj = new \stdClass();
                        $sortObj->value = $sortField->getValue();
                        $sortObj->text = Text::_($sortField->getName());
                        $sortObj->edition = $sortField->getEdition();
                        $sortFields[] = $sortObj;
                    }
                }
            }
        }

        return $sortFields;
    }

    /**
     * Get the type of the dynamic filter
     *
     * @return string
     * @throws \ReflectionException
     * @since 1.3.0
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