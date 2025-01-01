<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\AbstractCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered as OptionCollectionFiltered;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Field as FieldOption;

class Field extends OptionCollectionFiltered
{
    /**
     * @internal
     * @var string
     */
    protected $itemObjectClass = FieldOption::class;

    /**
     * Override the function to set custom ordering (if defined)
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    protected function init(): AbstractCollection
    {
        parent::init();
        $sortBy = $this->getFilterItem()->getAttributes()->get('options_sort_by');


        // If we order based on the field values order
        if ($sortBy == 'ordering' && $this->getFilterItem()->getConfig()->isDynamic() && method_exists($this->getFilterItem(), 'getParams')) {
            /** @var \Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilter\FieldsFilter $filter */
            $filter = $this->getFilterItem();
            $sortDirection = strtoupper($this->getFilterItem()->getAttributes()->get('options_sort_direction'));
            $sortDirection = in_array($sortDirection, ['ASC', 'DESC']) ? $sortDirection : 'ASC';
            $optionLabels = [];
            $options = $filter->getParams()->get('options');

            if (!empty($options)) {
                foreach ($options as $option) {
                    $option = (object)$option;
                    $optionLabels[] = $option->value;
                }
                $sortBy = 'label';
            }

            $this->setOrderField($sortBy, $optionLabels);
            $this->setOrderDir($sortDirection);
        }

        return $this;
    }
}
