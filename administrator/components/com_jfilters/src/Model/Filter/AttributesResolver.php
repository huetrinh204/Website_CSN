<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Form\FormField;

class AttributesResolver
{
    /**
     * @var FormFactoryInterface
     * @since 1.0.0
     */
    protected $formFactory;

    /**
     * @var ComponentConfig
     * @since 1.5.0
     */
    protected $componentConfig;

    /**
     * @var FormField[]
     * @since 1.0.0
     */
    protected $fields;

    /**
     * AttributesResolver constructor.
     *
     * @param   FormFactoryInterface  $formFactory
     * @param   ComponentConfig       $componentConfig
     */
    public function __construct(FormFactoryInterface $formFactory, ComponentConfig $componentConfig)
    {
        $this->formFactory = $formFactory;
        $this->componentConfig = $componentConfig;
    }

    /**
     * Sets the missing attributes with a default value
     *
     * @param   FilterInterface  $filter
     * @param   array            $fieldSetNames
     *
     * @return FilterInterface
     * @since 1.0.0
     */
    public function resolveEmptyToDefaults(FilterInterface $filter, $fieldSetNames = ['basic-attribs', 'tree-attribs', 'seo-attribs']) : FilterInterface
    {
        $this->fields = $this->getFieldSet($filter->getData(), $fieldSetNames);
        $attributes = $filter->getAttributes();
        // add the isTree to the attributes. Used by js
        $attributes->set('isTree', $filter->getConfig()->getValue()->getIsTree() ? true : false);

        /**
         * This is a nasty workaround as the 'isPro' is a component level attribute.
         * We assign it to the filter level so that can be used by the modules.
         */
        $attributes->set('isPro', $this->componentConfig->get('isPro'));
        $attributesArray = $attributes->toArray();
        foreach ($this->fields as $field) {
            if (!isset($attributesArray[$field->getAttribute('name')])) {
                $attributes->set($field->getAttribute('name'), $field->getAttribute('default', ''));
            }
        }
        return $filter;
    }

    /**
     * Get the requested field sets from the filter form
     *
     * @param $data
     *
     * @return array
     * @since 1.0.0
     */
    protected function getFieldSet($data, $fieldSetNames): array
    {
        $fields = [];
        Form::addFormPath(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jfilters' . DIRECTORY_SEPARATOR . 'forms');
        /** @var Form $form */
        $form = $this->formFactory->createForm('com_jfilters.filter', ['control' => 'jform']);
        $form->loadFile('filter', false);
        $form->bind($data);
        foreach ($fieldSetNames as $fieldSetName) {
            $fields = array_merge($fields, $form->getFieldset($fieldSetName));
        }

        return $fields;
    }
}
