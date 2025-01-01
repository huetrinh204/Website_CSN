<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\LanguageHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Class Declarative
 * generate static/non dynamic filters from config
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator
 */
class Declarative
{
    /**
     * @var FilterInterface
     * @since 1.0.0
     */
    protected $filterConfig;

    /**
     * @var LanguageHelper
     * @since 1.0.0
     */
    protected LanguageHelper $languageHelper;


    /**
     * Declarative constructor.
     * @param FilterInterface $filterConfig
     */
    public function __construct(FilterInterface $filterConfig, LanguageHelper $languageHelper)
    {
        $this->filterConfig = $filterConfig;
        $this->languageHelper = $languageHelper;
    }

    /**
     * Create a declarative/static filter
     * Declarative is a single filter with all it's data taken from the configuration
     *
     * @return array
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function generate() : array
    {
        $filters = [];
        $languages = [$this->filterConfig->getValue()->getLanguage()->getValue()];
        $languages = array_filter($languages);

        // if not declared in the config
        if(empty($languages)) {
            // Trace the used languages from the values/options
            $languages = $this->languageHelper->getLanguages();

            // Possibly no values, yet. Hence no languages.
            if(empty($languages)) {
                $languages = ['*'];
            }
        }

        $definition = $this->filterConfig->getDefinition();
        foreach ($languages as $language) {
            $filter = new \stdClass();
            $filter->parent_id = $definition->getId()->getValue();
            $filter->config_name = $this->filterConfig->getName();
            $filter->name = $definition->getTitle()->getValue();
            $attributes = [];
            // Default attributes for root elements
            if ($this->filterConfig->isRoot()) {
                $attributes['show_clear_option'] = 0;
                $filter->display = 'links';
            }

            if ($this->filterConfig->getValue()->getIsTree()) {
                $attributes['isTree'] = 1;
            }

            // Set data type 'date'. The 'dataType' attribute is used in the edit form
            if ($this->filterConfig->getValue()->getValue()->getType() == 'date') {
                $attributes['dataType'] = 'date';
            }

            $filter->attribs = new Registry($attributes);
            $filter->context = $definition->getContext()->getValue();
            $filter->root = $this->filterConfig->isRoot();

            // Set the root filters in listening state by default
            if($filter->root) {
                $filter->state = 2;
            }
            $filter->language = $language;
            $filters [] = $filter;
        }

        // Load the jfilters plugins, perhaps they want to do something
        PluginHelper::importPlugin('jfilters');
        Factory::getApplication()->triggerEvent('onFiltersAfterGenerate', [$this->filterConfig, &$filters]);
        return $filters;
    }
}
