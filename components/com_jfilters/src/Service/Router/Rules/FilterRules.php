<?php

namespace Bluecoder\Component\Jfilters\Site\Service\Router\Rules;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Helper\OptionsHelper;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as FilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection as OptionCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered\Nested;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Exception;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\RulesInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;

/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */
class FilterRules implements RulesInterface
{
    /**
     * The delimiter that separates the values in the same path.
     * Can be anything except the URI's general delimiters.
     * @see https://tools.ietf.org/html/rfc3986#section-2.2
     */
    protected const PATH_VALUES_DELIMITER = '|';

    /**
     * Router this rule belongs to
     *
     * @var    RouterView
     * @since  1.0.0
     */
    protected $router;

    /**
     * The filters collection
     *
     * @var FilterCollection
     * @since 1.0.0
     */
    protected $filterCollection;

    /**
     * These are reserved query vars in Joomla.
     *
     * @var array
     * @since 1.0.0
     * Make sure that those are not used as aliases. Otherwise there can be an issue with the way Joomla uses them, including fatal errors (e.g. format).
     */
    const RESERVED_QUERY_VAR_NAMES = [
        'option',
        'view',
        'controller',
        'Itemid',
        'layout',
        'lang',
        'language',
        'tmpl',
        'limitstart',
        'limit',
        'format'
    ];

    /**
     * FilterRules constructor.
     *
     * @param   RouterView  $router
     *
     * @throws \ReflectionException
     */
    public function __construct(RouterView $router)
    {
        $this->router = $router;
        $this->filterCollection = ObjectManager::getInstance()->getObject(FilterCollection::class);
        $this->filterCollection->addCondition('filter.state', [1, 2]);
        $menuItem = $this->router->menu->getActive();
        $context = $menuItem ? $menuItem->getParams()->get('contextType', 'com_content.article') : 'com_content.article';
        $this->filterCollection->addCondition('filter.context', $context);
        if (Multilanguage::isEnabled()) {
            $this->filterCollection->addCondition('filter.language',
                [Factory::getApplication()->getLanguage()->getTag(), '*'], '=');
        }
    }

    /**
     * Fulfill interface requirement.
     *
     * @param   array  $query
     *
     * @since 1.0.0
     */
    public function preprocess(&$query)
    {
    }

    /**
     * Parse the menu-less path, into vars.
     *
     * @param array $segments
     * @param array $vars
     *
     * @throws Exception
     * @since 1.0.0
     */
    public function parse(&$segments, &$vars)
    {
        $menuItem = $this->router->menu->getActive();
        $nesting_level = 0;

        foreach ($segments as $key => $segment) {
            // A segment is either a var alias or a value. Find out what it is.
            /** @var FilterInterface $filterItem */
            if (!isset($filterItem)) {
                $filterItem = $this->filterCollection->getByAttribute('alias', $segment);
                if ($filterItem) {
                    $varKey = $key;
                    continue;
                }
                $nesting_level = 0;
            }

            // if this is the 1st segment and there is no var alias, we use the menu's primary filter
            if (!$filterItem && $key == 0 && $menuItem && $menuItem->getParams()->get('primary_filtr', 0)) {
                $filterItem = $this->filterCollection->getByAttribute('id',
                    $menuItem->getParams()->get('primary_filtr', 0));
            }

            //  check for the value
            if ($filterItem) {
                $vars[$filterItem->getRequestVarName()] = $this->getValueFromSegment($filterItem, $segment);
                if (isset($varKey) && isset($segments[$varKey])) {
                    unset($segments[$varKey]);
                }
                if (isset($segments[$key])) {
                    unset($segments[$key]);
                }

                // it allows nesting in url. Maybe the next segment is a value under the same $filterItem
                if ($filterItem->getConfig()->getValue()->getIsTree() && (int)$filterItem->getAttributes()->get('max_path_nesting_levels', 2) > 1) {
                    $filterItemTmp = $filterItem;
                }
                $filterItem = null;
                $nesting_level++;
            }
            // there is nesting
            /** @var FilterInterface $filterItemTmp */
            elseif (isset($filterItemTmp) && $nesting_level < (int)$filterItemTmp->getAttributes()->get('max_path_nesting_levels',
                    2)) {
                $vars[$filterItemTmp->getRequestVarName()] = $this->getValueFromSegment($filterItemTmp, $segment);
                unset($segments[$key]);
                $nesting_level++;
            }
        }
    }

    /**
     * Get proper value/s from segment.
     *
     * @param   FilterInterface  $filterItem
     * @param   string           $segment
     *
     * @return string[]
     * @throws Exception
     * @since 1.0.0
     */
    protected function getValueFromSegment(FilterInterface $filterItem, string $segment)
    {
        $valueSegments = explode(urldecode(self::PATH_VALUES_DELIMITER), $segment);

        // there is no alias. Use the value as is
        if ($filterItem->getOptions() === null || $filterItem->getOptions()->getOptionConfig()->getAlias() === null) {
            return $valueSegments;
        }

        $values = [];
        $optionCollection = $filterItem->getOptions();
        $optionsHelper = OptionsHelper::getInstance();
        $optionsHelper->setOptionsLanguage($optionCollection);

        // cross filtering makes no sense at that stage. No input vars set yet.
        if ($optionCollection instanceof Filtered) {
            $optionCollection->setUseOtherSelectionsAsConditions(false);
        }
        // in case of nested options we do want the non nested version.
        if ($optionCollection->getOptionConfig()->getIsTree() && is_callable([
                $optionCollection,
                'getNonNestedOptions'
            ])) {
            $optionCollection = $optionCollection->getNonNestedOptions();
        }

        foreach ($valueSegments as $valueSegment) {
            /** @var OptionInterface $option */
            $option = $optionCollection->getByAttribute('alias', $valueSegment);
            $values[] = $option ? $option->getValue() : '';
        }

        return $values;
    }

    /**
     * Build the sef url
     *
     * @param   array  $query
     * @param   array  $segments
     *
     * @throws Exception
     * @since 1.0.0
     */
    public function build(&$query, &$segments)
    {
        // Get the menu item belonging to the Itemid that has been found
        $menuItem = $this->router->menu->getItem($query['Itemid']);

        /*
         * Component do not match the menu item
         * or view is not set
         */
        if ($menuItem && ($menuItem->component !== 'com_' . $this->router->getName() || !isset($menuItem->query['view']))) {
            return;
        }

        $query = $this->normalizeQuery($query);
        $query = $this->unsetPresetFilters($query);

        foreach ($query as $key => $value) {
            // we are looking only for filters
            if (in_array($key, self::RESERVED_QUERY_VAR_NAMES)) {
                continue;
            }

            /** @var FilterInterface $filterItem */
            $filterItem = $this->filterCollection->getByAttribute('alias', $key);
            if ($filterItem && $filterItem->getAttributes()->get('show_in_url', 'path') == 'path') {
                $valueSegment = $this->getSegmentFromValue($filterItem, $value);
                // not a menu set filter
                if (!$menuItem || $menuItem->getParams()->get('primary_filtr', 0) != $filterItem->getId()) {
                    $segments[] = $filterItem->getAlias();
                    $segments[] = $valueSegment;
                } elseif ($menuItem) {
                    // if it is the menu's primary filter, should be the 1st var in the path.
                    array_unshift($segments, $valueSegment);
                }
                unset($query[$key]);
            }
        }
    }

    /**
     * Remove the filters (from urls) which are preset in the menu item.
     * Those filters are injected in the component anyway. Otherwise, they will be duplicates.
     *
     * @param $query
     * @return array
     * @since 1.15.0
     */
    protected function unsetPresetFilters($query)
    {
        $menuItem = $this->router->menu->getItem($query['Itemid']);
        $presetFiltersStr = $menuItem ? $menuItem->getParams()->get('selected_filters') : '';

        if ($presetFiltersStr) {
            // dummy url just to achieve a proper parse_url()
            $url = 'https://example.com?' . $presetFiltersStr;
            $urlComponents = parse_url($url);
            if ($urlComponents && isset($urlComponents['query'])) {
                parse_str($urlComponents['query'], $presetQueryParams);
                foreach ($presetQueryParams as $key => $value) {
                    if (!empty($query[$key])) {
                        $queryValue = is_array($query[$key]) ? $query[$key] : [$query[$key]];
                        $value = is_array($value) ? $value : [$value];
                        $toBeSetValues = array_diff($queryValue, $value);
                        unset($query[$key]);
                        if ($toBeSetValues) {
                            $query[$key] = $toBeSetValues;
                        }
                    }
                }
            }
        }

        return $query;
    }

    /**
     * Normalize query for the vars which are arrays but shown as strings (e.g. 'type[0]' => 'something')
     *
     * @param $query
     *
     * @return array
     * @since 1.0.0
     */
    protected function normalizeQuery($query)
    {
        foreach ($query as $key => $value) {
            // we are looking only for filters
            if (in_array($key, self::RESERVED_QUERY_VAR_NAMES)) {
                continue;
            }

            // it returns the array var names as 'var[]'
            if (strpos($key, '[')) {
                $varName = preg_replace('/\[[0-9]*\]/', '', $key);
                if (!isset($query[$varName])) {
                    $query[$varName] = [];
                }
                is_array($value) ? $query[$varName] = $value : $query[$varName][] = $value;
                unset($query[$key]);
            }
        }

        return $query;
    }

    /**
     * Return the value that will be used in the router uri, for an option
     *
     * @param   FilterInterface  $filterItem
     * @param   string|array     $value
     *
     * @return string
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function getSegmentFromValue(FilterInterface $filterItem, $value): string
    {
        // convert string values to array. This way we handle them all the same way.
        $values = [$value];
        if (is_array($value)) {
            $values = $value;
        }
        $newValues = [];
        $nestingLevel = 1;
        $optionCollection = $filterItem->getOptions();
        $optionsHelper = OptionsHelper::getInstance();
        $optionsHelper->setOptionsLanguage($optionCollection);

        // in case of nested options we do want the non nested version.
        if ($optionCollection->getOptionConfig()->getIsTree() && $optionCollection instanceof Nested) {
            $optionCollection = $optionCollection->getNonNestedOptions();
        }

        // there is alias. Use that instead of the plain values.
        if ($optionCollection->getOptionConfig()->getAlias()) {
            foreach ($values as $val) {
                /** @var OptionInterface $option */
                $option = $optionCollection->getByAttribute('value', $val);
                $newValue = $option && $option->getAlias() ? $option->getAlias() : $val;

                // take care for nested options
                while (!$filterItem->getIsMultiSelect() && $nestingLevel < (int)$filterItem->getAttributes()->get('max_path_nesting_levels',2)) {
                    $nestingLevel++;
                    if (isset($option) && method_exists($option, 'getParentOption') && $option->getParentOption() !== null && $option->getParentOption()->getAlias()) {
                        $newValue = $option->getParentOption()->getAlias() . '/' . $newValue;
                        $option = $option->getParentOption();
                    }
                }
                $newValues[] = $newValue;
            }
        } // no alias for the values
        else {
            $newValues = $values;
        }

        // Calendars and range inputs can receive dates outside our $optionCollection
        if ($filterItem->getDisplay() !== 'calendar' && !$filterItem->getIsRange()) {
            $newValues = $this->sortValues($optionCollection, $newValues);
        }

        return implode((self::PATH_VALUES_DELIMITER), $newValues);
    }

    /**
     * Sort the url values based on the filter options ordering
     *
     * @param   OptionCollection  $optionCollection
     * @param                     $values
     *
     * @return array|string
     * @since 1.0.0
     */
    protected function sortValues(OptionCollection $optionCollection, $values)
    {
        if (is_array($values) && count($values) > 1) {

            // Do not get fooled by the letter case
            $values = array_map(function ($value) {
                return mb_strtolower($value);
            }, $values);

            $newValues = [];
            /** @var  OptionInterface $option */
            foreach ($optionCollection as $option) {
                $value = $optionCollection->getOptionConfig()->getAlias() ? $option->getAlias() : urlencode($option->getValue());
                if (in_array(mb_strtolower($value), $values) || in_array(mb_strtolower($option->getValue()), $values)) {
                    $newValues[] = $value;
                }
            }
            $values = $newValues;
        }

        return $values;
    }
}
