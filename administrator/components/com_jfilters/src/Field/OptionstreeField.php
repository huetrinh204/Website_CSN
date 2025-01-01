<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Helper\OptionsHelper;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered\Nested;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Form\Field\ListField;

class OptionstreeField extends ListField
{
    /**
     * @return array
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function getOptions()
    {
        $returnedOptions = [];
        $data = $this->form->getData();
        $name = $data->get('name');
        $objectManager = ObjectManager::getInstance();
        /** @var  Collection $filterCollection */
        $filterCollection = $objectManager->getObject(Collection::class);
        $filterCollection->addCondition('filter.name', $name);

        if ($filterCollection->getSize() == 1) {
            $items = $filterCollection->getItems();
            /** @var FilterInterface $item */
            $item = reset($items);

            // Reset the root, otherwise it will return the sub-tree of the selected root.
            $item->getAttributes()->set('root_option', 1);
            $options = $item->getOptions();

            // it is a nested collection and is not empty
            if ($options instanceof Nested && $options->getSize() > 0) {
                $optionsHelper = OptionsHelper::getInstance();
                $optionItems = $optionsHelper->getFullTree($options);

                /** @var OptionInterface $option */
                foreach ($optionItems as $option) {
                    $optionStdObject = new \stdClass();
                    $optionStdObject->value = $option->getValue();
                    $optionStdObject->text = $option->getLabel();
                    $returnedOptions [] = $optionStdObject;
                }
            }
        }

        return array_merge(parent::getOptions(), $returnedOptions);
    }
}
