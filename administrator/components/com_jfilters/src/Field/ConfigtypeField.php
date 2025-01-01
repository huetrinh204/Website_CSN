<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Field;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as FilterConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

/**
 * Class ContextFieldField- used for displaying the existing contexts
 *
 */
class ConfigtypeField extends ListField
{
    /**
     * Method to get the field options.
     *
     * @return array
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function getOptions()
    {
        $objectMager = ObjectManager::getInstance();
        /** @var  FilterConfigCollection $contextCollection */
        $filterConfigCollection = $objectMager->getObject(FilterConfigCollection::class);

        $options        = [];
        $options_buffer = [];
        /** @var FilterInterface $filterConfig */
        foreach ($filterConfigCollection as $filterConfig) {
            $configName = $filterConfig->getName();
            if (isset($options_buffer[$configName])) {
                continue;
            }
            $label                       = self::createLabel($configName, true);
            $options[]                   = ['value' => $configName, 'text' => $label];
            $options_buffer[$configName] = $label;
        }

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Creates a label string from the config name
     *
     * @param $configName
     *
     * @return string
     * @since 1.0.0
     */
    public static function createLabel($configName, $withContext = false)
    {
        $label = 'COM_JFILTERS_CONFIG_TYPE_' . strtoupper($configName);
        try {
            /** @var  FilterConfigCollection $filtersConfigCollection */
            $filtersConfigCollection = ObjectManager::getInstance()->getObject(FilterConfigCollection::class);
            /** @var  FilterInterface $configFilter */
            $configFilter = $filtersConfigCollection->getByNameAttribute($configName);
            if ($configFilter && $configFilter->getLabel()) {
                $label    = $configFilter->getLabel();
                $language = Factory::getApplication()->getLanguage();
                $language->load(strtolower($label), JPATH_ADMINISTRATOR);
                $label = Text::_(trim(strtoupper($label)));

                /** @var  ContextCollection $contextCollection */
                $contextCollection = ObjectManager::getInstance()->getObject(ContextCollection::class);
                $context = $contextCollection->getByNameAttribute($configFilter->getDefinition()->getContext()->getValue());
                if ($context && $withContext) {
                    $label .= ' - ' . Text::_($context->getAlias());
                }
            }
        } catch (\Exception $exception) {
            //suck it. No big deal.
        }


        return $label;
    }
}
