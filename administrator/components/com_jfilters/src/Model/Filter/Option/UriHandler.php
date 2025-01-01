<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Helper\OptionsHelper;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as FilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule\Collection as SortingRuleCollection;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Class Route
 * It generates the option link/url
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option
 * @since 1.0.0
 */
class UriHandler implements UriHandlerInterface
{
    /**
     * @var FilterCollection
     * @since 1.0.0
     */
    protected FilterCollection $filterCollection;

    /**
     * @var Uri
     * @since 1.0.0
     */
    protected Uri $uri;

    /**
     * Store the filter vars, in the order they are in the collection.
     *
     * @var array|null
     * @since 1.0.0
     */
    protected ?array $filterVars = null;

    /**
     * Stores the filters whose requests are cleared from invalid values.
     * Used as a storage for non executing the same functions.
     *
     * @var array
     * @since 1.0.0
     */
    protected static array $filtersRequestCleared = [];

    /**
     * @var SortingRuleCollection
     * @since 1.16.0
     */
    protected SortingRuleCollection $sortingRuleCollection;

    /**
     * @param FilterCollection $filterCollection
     * @param SortingRuleCollection $sortingRuleCollection
     * @param Uri $uri
     * @since 1.0.0
     */
    public function __construct(FilterCollection $filterCollection, SortingRuleCollection $sortingRuleCollection, Uri $uri)
    {
        $this->filterCollection = $filterCollection;
        $this->filterCollection->addCondition('filter.state', [1, 2]);
        $this->sortingRuleCollection = $sortingRuleCollection;
        $this->uri = $uri;
    }

    /**
     * It creates the base uri/link for an option.
     * A base uri, contains the vars from others than the current filter. It is used when a filter is cleared.
     *
     * @param   OptionInterface  $option
     *
     * @return Uri
     * @throws \Exception
     * @since 1.0.0
     */
    public function getBase(OptionInterface $option): Uri
    {
        $base = 'index.php?option=' . UriHandlerInterface::COMPONENT . '&view=' . UriHandlerInterface::VIEW;
        // needs to be reset. Otherwise, it gets the last generated uri with that base.
        $this->uri::reset();
        $uri = $this->uri::getInstance($base);
        $this->setFinderVars($uri);

        /*
         * If the filter is not root, we can include vars from the other selected filters
         */
        if (!$option->getParentFilter()->getRoot()) {
            /** @var FilterInterface $filter */
            foreach ($this->filterCollection as $filter) {

                // The current filter's vars, skip.
                if ($filter->getId() == $option->getParentFilter()->getId()) {
                    continue;
                }
                $this->removeInvalidRequestValues($filter);
                $values = $filter->getRequest();

                if (!empty($values)) {
                    $values = $this->urlEncodeArrayValues($values);
                    $isMultiSelect = $filter->getIsMultiSelect() || $filter->getIsRange();

                    if (!$isMultiSelect || count($values) == 1) {
                        $values = reset($values);
                    }
                    $varName = $filter->getRequestVarName();
                    $uri->setVar($varName, $values);
                }
            }
        }
        $this->setOrderingVars($uri);

        return $uri;
    }

    /**
     * Creates the final uri/link for an option
     * @since 1.0.0
     */
    public function get(OptionInterface $option, bool $toggleVar = true): Uri
    {
        $uri = $this->getBase($option);
        $this->setFinderVars($uri);
        $current = $option->getValue();
        $requested = urlencode($current);

        $isMultiSelect = $option->getParentFilter()->getIsMultiSelect() || ($option->getParentFilter()->getDisplay() == 'calendar' && $option->getParentFilter()->getAttributes()->get('calendar_mode') == 'range') ? true : false;

        // add the other selections from the same filter, for multiselect
        if ($isMultiSelect && (!$option->isNested() || $option->isNested() && !$option->getChildren())) {
            $this->removeInvalidRequestValues($option->getParentFilter());
            // in case of nested tree we do not want both the child and the parent selected
            if ($option->getParentFilter()->getConfig()->getValue()->getIsTree()) {
                $this->removeParentOptionVar($option);
            }

            // Do not get fooled by the letter case
            $requested = array_map(function ($requestValue) {
                return mb_strtolower($requestValue);
            }, $option->getParentFilter()->getRequest());

            /*
             * Create the toggle effect for the multi-select.
             * Remove it if exists, add it otherwise.
             */
            $position = array_search(mb_strtolower($current), $requested);
            if ($toggleVar && $position !== false) {
                unset($requested[$position]);
            } elseif($position === false && !empty($current)) {
                $requested [] = $current;
            }
            $requested = $this->urlEncodeArrayValues($requested);
        }
        if ($requested) {
            // not array but string when only 1 element
            if (is_array($requested) && count($requested) == 1) {
                $requested = reset($requested);
            }
            $uri->setVar($option->getParentFilter()->getRequestVarName(), $requested);
        }

        return $this->sortUriVars($uri);
    }

    /**
     * Url encode array
     *
     * @param   array  $values
     *
     * @return array
     * @since 1.0.0
     */
    protected function urlEncodeArrayValues(array $values): array
    {
        return array_map(function ($value) {
            return $value !== null ? urlencode($value) : '';
        }, $values);
    }

    /**
     * Set the ordering vars to the uri
     *
     * @param Uri $uri
     * @return $this
     * @throws \Exception
     * @since 1.16.0
     */
    protected function setOrderingVars(Uri $uri) : UriHandler
    {
        $input = Factory::getApplication()->getInput();
        if ($input->getCmd('option', '') === UriHandlerInterface::COMPONENT) {
            $sortingRules = $this->sortingRuleCollection->getItems();
            /** @var SortingRule $currentSortingRule */
            $currentSortingRule = $this->sortingRuleCollection->getCurrent();
            // Apply only if needed. If it's the 1st rule and is the current, will be applied anyway.
            if ($sortingRules && $currentSortingRule != reset($sortingRules)) {
                $uri->setVar('o', $currentSortingRule->getSortField()->getFieldName());
                if ((empty($uri->getVar('q')) && SortingRule::DEFAULT_SORTING_FILTERING_DIR != $currentSortingRule->getSortDirection())
                || (!empty($uri->getVar('q')) && SortingRule::DEFAULT_SORTING_SEARCH_DIR != $currentSortingRule->getSortDirection())) {
                    $uri->setVar('od', strtolower($currentSortingRule->getSortDirection()));
                }
            }
        }

        return $this;
    }

    /**
     * Set the request vars of com_finder
     *
     * @param   Uri  $uri
     *
     * @return $this
     * @throws \Exception
     * @since 1.0.0
     */
    protected function setFinderVars(Uri $uri) : UriHandler
    {
        // set the query if exists
        if ($searchQuery = Factory::getApplication()->getInput()->request->getString('q')) {
            $uri->setVar('q', urlencode($searchQuery));
        }
        // Get the static taxonomy filters.
        if($taxonomy = Factory::getApplication()->getInput()->request->getInt('f')) {
            $uri->setVar('f', $taxonomy);
        }

        // Get the dynamic taxonomy filters.
        if($taxonomyDynamic = Factory::getApplication()->getInput()->request->getInt('t')) {
            $uri->setVar('t', $taxonomyDynamic);
        }

        // Get the language.
        if($language = Factory::getApplication()->getInput()->request->getCmd('l')) {
            $uri->setVar('l', $language);
        }

        // Get the start date and start date modifier filters.
        $var = 'd1';
        if($d1 = Factory::getApplication()->getInput()->request->getString($var)) {
            $uri->setVar($var, $d1);
        }

        $var = 'w1';
        if($w1 = Factory::getApplication()->getInput()->request->getString($var)) {
            $uri->setVar($var, $w1);
        }

        // Get the end date and end date modifier filters.
        $var = 'd2';
        if($d2 = Factory::getApplication()->getInput()->request->getString($var)) {
            $uri->setVar($var, $d2);
        }

        $var = 'w2';
        if($w2 = Factory::getApplication()->getInput()->request->getString($var)) {
            $uri->setVar($var, $w2);
        }

        return $this;
    }

    /**
     * Remove the parent option var from the requests' var.
     * We do not want both the parent and the child in the url.
     *
     * @param   OptionInterface  $option
     *
     * @return $this
     * @throws \Exception
     * @since 1.0.0
     */
    protected function removeParentOptionVar(OptionInterface $option): UriHandler
    {
        $request = $option->getParentFilter()->getRequest();
        if ($option->isNested() && $option->getParentOption()) {
            $foundParent = array_search($option->getParentOption()->getValue(), $request);
            if ($foundParent !== false) {
                unset($request[$foundParent]);
                $option->getParentFilter()->setRequest($request);
            }

            // do it for 1 more level up
            $this->removeParentOptionVar($option->getParentOption());
        }

        return $this;
    }

    /**
     * Removes request values that do no longer exist in the filter's options.
     *
     * @param  FilterInterface  $filter
     *
     * @return $this
     * @throws \Exception
     * @since 1.0.0
     */
    protected function removeInvalidRequestValues(FilterInterface $filter): UriHandler
    {
        if (!in_array($filter->getId(), self::$filtersRequestCleared) && $filter->getRequest()) {
            /** @var Collection $options */
            $options = $filter->getOptions();
            $optionsHelper = OptionsHelper::getInstance();

            /*
            * Set the language also for the options.
            * Some filters may have multi-lingual options (e.g. categories)
            */
            $optionsHelper->setOptionsLanguage($options);

            if ($options->getOptionConfig()->getIsTree() && is_callable([$options, 'getNonNestedOptions'])) {
                $optionsTmp = $options->getNonNestedOptions();
                if ($optionsTmp) {
                    $options = $optionsTmp;
                }
            }
            $requests = $filter->getRequest();
            // Unset non-existent for display types that has defined options/values
            if (!$filter->getIsRange()) {
                foreach ($requests as $key => $request) {
                    $found = $options->getByAttribute('value', (string)$request);
                    if ($found === null) {
                        unset($requests[$key]);
                    }
                }
            }

            $filter->setRequest($requests);
            self::$filtersRequestCleared[] = $filter->getId();
        }

        return $this;
    }

    /**
     * Sort vars based on the filters' order params
     *
     * @param   Uri  $uri
     *
     * @return Uri
     * @since 1.0.0
     */
    protected function sortUriVars(Uri $uri): Uri
    {
        $sortList = array_merge(['option', 'view'], $this->getFilterRequestVars());
        $queryInit = $uri->getQuery(true);
        $queryFiltered = array_filter($queryInit, function ($value, $key) use ($sortList) {
            return in_array($key, $sortList);
        }, ARRAY_FILTER_USE_BOTH);
        foreach ($sortList as $sortItem) {
            if (@isset($queryFiltered[$sortItem])) {
                $value = $queryFiltered[$sortItem];
                unset($queryFiltered[$sortItem]);
                $queryFiltered[$sortItem] = $value;
            }
        }
        $query = $queryFiltered;
        // add the non mentioned keys/vars at the end
        if(count($queryInit) != count($queryFiltered)) {
            $notIncludedVars = array_diff_key($queryInit, $queryFiltered);
            $query = array_merge($queryFiltered, $notIncludedVars);
        }

        $uri->setQuery($query);

        return $uri;
    }

    /**
     * Get the filter request vars, in the order they are in the collection.
     *
     * @return array|null
     * @since 1.0.0
     */
    protected function getFilterRequestVars(): ?array
    {
        if ($this->filterVars == null) {
            $this->filterVars = [];
            /** @var FilterInterface $filter */
            foreach ($this->filterCollection as $filter) {
                $this->filterVars[] = $filter->getRequestVarName();
            }
        }

        return $this->filterVars;
    }
}
