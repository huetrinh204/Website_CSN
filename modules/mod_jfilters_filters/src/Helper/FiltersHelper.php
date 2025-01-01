<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Module\JfiltersFilters\Site\Helper;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Helper\OptionsHelper;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as FilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered\Nested;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Menu\MenuItem;
use Joomla\Registry\Registry;

/**
 * Class FiltersHelper
 *
 * Operates on filters
 *
 * @package Bluecoder\Module\JfiltersFilters\Site\Helper
 */
class FiltersHelper
{
    /**
     * @var null|array
     * @since 1.0.0
     */
    protected $filtersActive;

    /**
     * @var LoggerInterface
     * @since 1.0.0
     */
    protected LoggerInterface $logger;

    /**
     * @var Registry
     * @since 1.0.0
     */
    protected Registry $params;

    /**
     * All the active filters
     *
     * @var array|null
     * @since 1.12.0
     */
    protected ?array $filters;

    /**
     * @var MenuItem|null
     * @since 1.15.0
     */
    protected ?MenuItem $jfMenuItem = null;

    /**
     * FiltersHelper constructor.
     *
     * @param $params
     *
     * @throws \ReflectionException
     */
    public function __construct(&$params)
    {
        $this->logger = ObjectManager::getInstance()->getObject(LoggerInterface::class);
        $this->params = $params;
    }

    /**
     * Return the module's filters
     *
     * @return FilterInterface[]
     * @throws \Exception
     * @since 1.0.0
     */
    public function getList()
    {
        if ($this->filtersActive === null) {
            /** @var FilterCollection $filtersCollection */
            $filtersCollection = ObjectManager::getInstance()->getObject(FilterCollection::class);
            // We used to store the 'context' in the module's param. This is changed since 1.1.5.0
            $context = $this->getRelatedMenuItem() ? $this->getRelatedMenuItem()->getParams()->get('contextType', $this->params->get('context', 'com_content.article')) : 'com_content.article';
            try {
                // We are calling both the 'published' and the 'listening' filters, to avoid reloading the collection. We filter only the published then.
                $filtersCollection->addCondition('filter.state', [1, 2]);
                $filtersCollection->addCondition('filter.context', $context);
                if (Multilanguage::isEnabled()) {
                    $filtersCollection->addCondition('filter.language',
                        [Factory::getApplication()->getLanguage()->getTag(), '*'], '=');
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), ['category' => 'mod_jfilters_filters']);
            }

            $this->filtersActive = $filtersCollection->getItems();
            $this->filters =  $this->filtersActive;
            $this->removeNonVisibleFilters();
            $this->initOptions();
        }
        return $this->filtersActive;
    }

    /**
     * Remove filters which are not selected in the module's respective param (if not empty)
     *
     * @return $this
     * @throws \Exception
     * @since 1.0.0
     */
    protected function removeNonVisibleFilters() : self
    {
        $selected_type = $this->params->get('filters_selection', 'all');
        $selected = array_filter($this->params->get('filters', []));

        /** @var FilterInterface $filter */
        foreach ($this->filtersActive as $key => $filter) {

            if ($filter->getState() != 1) {
                unset($this->filtersActive[$key]);
                continue;
            }
            if ($selected_type == 'select' && !empty($selected)) {
                if (!in_array($filter->getId(), $selected)) {
                    unset($this->filtersActive[$key]);
                    continue;
                }
            } // Exclude
            elseif ($selected_type == 'exclude' && !empty($selected) && in_array($filter->getId(), $selected)) {
                unset($this->filtersActive[$key]);
                continue;
            }

            $this->setIsVisible($filter);

            /*
             * Remove non-visible, when no ajax is used.
             * When we use ajax, we have to load published filters, which currently may not be visible due to conditions.
             * We do that because we have to load their assets (e.g. calendars or ranges) beforehand.
             */
            if (!$this->params->get('ajax_mode', 0) && !$filter->isVisible()) {
                unset($this->filtersActive[$key]);
            }
        }

        return $this;
    }

    /**
     * Set if the filter will be shown in the module.
     * Do this by examining factors like state and display conditions.
     *
     * @param FilterInterface $filter
     * @return FiltersHelper
     * @throws \Exception
     */
    protected function setIsVisible(FilterInterface $filter) : self
    {
        $isVisible =  true;
        // Only published are shown
        if ($filter->getState() != 1) {
            $isVisible = false;
        }

        // Root filters cannot be part of the shown on functionality
        if ($isVisible === true && !$filter->getRoot() && !empty($filter->getAttributes()->get('show_on_selected_filters', []))) {
            $showOnFilters = $filter->getAttributes()->get('show_on_selected_filters', []);
            $showOnOperator = $filter->getAttributes()->get('show_on_operator', 'OR');

            /** @var  FilterInterface $filterTmp */
            foreach ($this->filters as $filterTmp)
            {
                // The filter is included in the condition
                if ($filterTmp->getId() != $filter->getId() && in_array($filterTmp->getId(), $showOnFilters)) {
                    $isVisible = !empty($filterTmp->getRequest());
                    if ($isVisible && $showOnOperator === 'OR') {
                        // Break as true. Even one is true
                        break;
                    }elseif (!$isVisible && $showOnOperator === 'AND') {
                        // Break as false. Even one is false
                        break;
                    }
                }
            }
        }

        if ($isVisible === false && $filter->getState() != 2) {
            // We do not want requests by non-visible filters to be used by the others (exception those in listening state).
            $filter->setRequest([]);
        }

        $filter->setIsVisible($isVisible);

        return $this;
    }

    /**
     * Set conditions to the filter options and format them accordingly
     *
     * @return $this
     * @throws \Exception
     * @since 1.0.0
     */
    protected function initOptions()
    {
        $optionsHelper = OptionsHelper::getInstance();
        /*
         * The context is removed from JF module's params, but we keep it for B/C reasons.
         */
        $context = $this->getRelatedMenuItem() ? $this->getRelatedMenuItem()->getParams()->get('contextType', $this->params->get('context', 'com_content.article')) : 'com_content.article';

        /** @var FilterInterface $filter */
        foreach ($this->filtersActive as $filter) {
            $options = $filter->getOptions();

            /*
             * Set the language also for the options.
             * Some filters may have multi-lingual options (e.g. categories)
             */
            $optionsHelper->setOptionsLanguage($options);

            /*
             * Set the query from the smart search as condition.
             * Make sure that you add that after any other condition,
             * this way if the search returns nothing the collection will not be  cleared and reloaded by another 'addCondition'
             */
            if ($this->params->get('use_smart_search', true)) {
                $optionsHelper->setSearchQueryResults($filter, $context);
            }

            // Use the selections from the other filters to get the options of that collection.
            $options->setUseOtherSelectionsAsConditions(true);

            // In case of tree, with set parent, load only the nodes of that parent (remove the rest).
            if ($filter->getConfig()->getValue()->getIsTree()) {

                // setHasChildSelectedToParent. No need to do it for the list display
                if ($options instanceof Nested && $filter->getDisplay() != 'list') {
                    $options->setHasChildSelectedToParent();
                }

                // Get the desired subtree, based on the 'root_option' setting
                if ($rootOptionValue = $filter->getAttributes()->get('root_option', 0)) {
                    // Get the children options of a selected parent or null
                    $options = $optionsHelper->getChildren($filter->getOptions(), $rootOptionValue);
                    // No relevant options in the current sub-tree
                    if ($options === null) {
                        $filter->getOptions()->clearItems(false);
                        continue;
                    }

                    // We set the non-nested collection to our new collection. Otherwise the tree will have to be re-built, later.
                    if ($filter->getOptions() instanceof Nested && $options instanceof Nested) {
                        $nonNested = $filter->getOptions()->getNonNestedOptions();
                        $options->setNonNestedOptions($nonNested);
                    }
                    $filter->setOptions($options);
                    $filter->setOptions($options);
                }
            }
        }

        /**
         * The uri generation for each filter, requires the Option Collections of all the selected filters to be loaded.
         * We use another foreach, as we set the conditions for all the Option Collections in the 1st foreach.
         * By doing that, the same Option Collections are used in the entire app, without the need to load new ones (with different conditions)
         */
        foreach ($this->filtersActive as $filter) {
            // Set the item id for each option uri
            if ($filter->getOptions() && $this->params->get('Itemid', 0)) {
                $optionCollection = $filter->getOptions();

                // in case of nested options we want the non nested version.
                if ($filter->getOptions()->getOptionConfig()->getIsTree() && $filter->getOptions() instanceof Nested) {
                    /** @var Nested $optionCollection */
                    $optionCollection = $optionsHelper->getFullTree($optionCollection, 0, '');
                }

                $optionsHelper->setOptionsItemId($optionCollection, $this->params->get('Itemid', 0));
            }

            // We want the full tree for the lists.
            if ($filter->getOptions() && $filter->getAttributes()->get('isTree', false) && $filter->getDisplay() == 'list') {
                $optionCollection = $filter->getOptions();
                /** @var Nested $nonNestedCollection */
                $nonNestedCollection = $optionsHelper->getFullTree($optionCollection);

                // This is already non-nested. No need to be processed again.
                $nonNestedCollection->setNonNestedOptions(clone $nonNestedCollection);
                $filter->setOptions($nonNestedCollection);
            }
        }

        return $this;
    }

    /**
     * Get the menu item related with the module
     *
     * @return MenuItem|null
     * @throws \Exception
     */
    protected function getRelatedMenuItem(): ?MenuItem
    {
        if ($this->jfMenuItem === null) {
            $menuItemId = $this->params->get('Itemid');
            if ($menuItemId) {
                /** @var MenuItem|null $jfMenuItem */
                $this->jfMenuItem = Factory::getApplication()->getMenu()->getItem($menuItemId);
            }
        }

        return $this->jfMenuItem;
    }
}
