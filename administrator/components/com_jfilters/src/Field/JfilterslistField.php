<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;

class JfilterslistField extends ListField
{

    use SetupTrait;
    use RenderFieldTrait;

    /**
     * @var FilterInterface
     * @since 1.0.0
     */
    protected $filter;

    /**
     * Method to get the field options with custom attributes.
     *
     * @return array
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function getOptions()
    {
        $options = parent::getOptions();

        foreach ($this->element->xpath('option') as $key => $optionArray) {
            $options[$key]->multiselect = $optionArray['multiselect'] == 'true';
            $options[$key]->range = $optionArray['range'] == 'true';
            $options[$key]->edition = $optionArray['edition']  ? (string) $optionArray['edition'] : 0;
            $options[$key]->dataType = $optionArray['dataType']  ? (string) $optionArray['dataType'] : '';
        }
        return $options;
    }

    /**
     * Disable the PRO options in FREE edition and add the 'PRO' label besides each PRO option.
     *
     * @param $options
     *
     * @return $this
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function adjustProOptions($options)
    {
        /** @var ComponentConfig $componentConfig */
        $componentConfig = ObjectManager::getInstance()->getObject(ComponentConfig::class);

        foreach ($options as &$option) {
            // Disable options in no Pro edition.
            if (!$componentConfig->get('isPro') && isset($option->edition) && (int)$option->edition == 100) {
                $option->disable = true;
                $option->text .= ' [PRO]';
            }
        }

        return $this;
    }

    /**
     * @param   FilterInterface  $filterObject
     *
     * @since 1.0.0
     */
    public function setFilter(FilterInterface $filterObject)
    {
        $this->filter = $filterObject;
    }

    /**
     * Get the filter object
     *
     * @return FilterInterface|null
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function getFilter(): ?FilterInterface
    {
        if ($this->filter === null) {
            $input = Factory::getApplication()->getInput();
            // we are in the edit page
            if ($input->get('option') == 'com_jfilters' && $input->get('view') == 'filter' && $input->get('id', 0,
                    'int')) {
                $filterId = $input->get('id', 0, 'int');
                /** @var Collection $filterCollection */
                $filterCollection = ObjectManager::getInstance()->getObject(Collection::class);
                $filterCollection->addCondition('filter.id', $filterId);
                if ($filterCollection->getSize() == 1) {
                    $items = $filterCollection->getItems();
                    $this->filter = reset($items);
                }
            }
        }

        return $this->filter;
    }
}
