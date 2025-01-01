<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\SortingRule;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\AbstractCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\MenuItemTrait;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

class Collection extends AbstractCollection
{
    use MenuItemTrait;

    /**
     * @var string
     * @since 1.16.0
     */
    protected $itemObjectClass = SortingRule::class;

    /**
     * @var array
     * @since 1.16.0
     */
    protected array $filterRulesCache = [];

    /**
     * @var SortingRule|null
     * @since 1.16.0
     */
    protected ?SortingRule $currentItem = null;

    /**
     * @var array|null
     * @since 1.16.0
     */
    protected $sortingRuleParams;

    /**
     * Generates the sortingRule Collection
     *
     * @return AbstractCollection
     * @throws \ReflectionException
     * @since 1.16.0
     */
    public function load(): AbstractCollection
    {
        if ($this->isLoaded) {
            return $this;
        }

        $isSearch = false;
        $app = Factory::getApplication();
        // In our unit tests, there is no `ConsoleApplication::getParams()`
        $finderParams = method_exists($app, 'getParams') ? $app->getParams('com_finder') : new Registry();
        if ($app->input->getString('q') || ($finderParams->get('allow_empty_query') && !empty($app->input->get('t')))) {
            $isSearch = true;
        }

        $sortingRules = $this->getSortingRuleParams();
        if ($sortingRules) {

            foreach ($sortingRules as $sortingRule) {
                // Sort by relevance cannot be used for filtering. 'm.weight' is used for B/C reasons
                $sortingRule->useOnFiltering = $sortingRule->sortField === 'm.weight' || $sortingRule->sortField === SortingRule::DEFAULT_SORTING_SEARCH_FIELD ? 0 : $sortingRule->useOnFiltering;

                // The rule is not applicable for the current search/filtering
                if (($isSearch && !$sortingRule->useOnSearch) || (!$isSearch && !$sortingRule->useOnFiltering)) {
                    continue;
                }
                $key = hash('md5', $sortingRule->sortField . '-' . $sortingRule->sortDirection . '-' .
                    $sortingRule->useOnSearch . '-' . $sortingRule->useOnFiltering . '-' . $sortingRule->conditionOperator . '-' . $sortingRule->conditionFilters);

                // A rule with exact same settings exists.
                if (isset($this->filterRulesCache[$key])) {
                    continue;
                }
                /** @var Condition $condition */
                $condition = $this->objectManager->createObject(Condition::class);
                $condition->setConditionOperator($sortingRule->conditionOperator);
                $condition->setConditionFilters($sortingRule->conditionFilters);

                if ($condition->isValid()) {
                    /** @var SortingRule $sortingRuleObj */
                    $sortingRuleObj = $this->objectManager->createObject($this->itemObjectClass);
                    $sortingRuleObj->setData($sortingRule);
                    $sortingRuleObj->setCondition($condition);
                    $this->addItem($sortingRuleObj);
                    $this->filterRulesCache[$key] = true;
                }
            }
        }

        $this->isLoaded = true;
        $this->isInitialized = true;

        // Nothing is found, but our results need a default sorting rule.
        if (empty($this->items)) {
            try {
                $sortingRuleObj = $this->getDefault();
                $this->addItem($sortingRuleObj);
            }
            catch (\Exception $exception) {
                /** @var LoggerInterface $logger */
                $logger = ObjectManager::getInstance()->getObject(LoggerInterface::class);
                $logger->error('No Sorting Rule Found and default cannot be created.' . $exception->getMessage());
            }

        }

        return parent::load();
    }

    public function clear(): AbstractCollection
    {
        parent::clear();
        $this->filterRulesCache = [];
        return $this;
    }

    /**
     * Gets the sorting rules from the menu item
     *
     * @return array|null
     * @throws \Exception
     * @since 1.16.0
     */
    protected function getSortingRuleParams() : ?array
    {
        if ($this->sortingRuleParams === null) {
            $this->sortingRuleParams = [];
            $app = Factory::getApplication();
            // In our unit tests, there is no `ConsoleApplication::getMenu()`
            $menuItem = method_exists($app, 'getMenu') ? $this->getMenuItem() : null;
            if ($menuItem) {
                $this->sortingRuleParams = $menuItem->getParams()->get('sorting_rules', []);
                $this->sortingRuleParams = (array)$this->sortingRuleParams;
            }
        }

        return $this->sortingRuleParams;
    }

    /**
     * Set the sorting params, so that the collection can be created.
     * The main reason for the existence of this fn, is testing.
     * There is no menu item to get the params during testing, and we need to inject them somehow.
     *
     * @param array $sortingRuleParams
     * @return $this
     * @since 1.16.0
     */
    public function setSortingRuleParams(array $sortingRuleParams) : Collection
    {
        // New params. Clear the collection
        $this->clear();
        $this->sortingRuleParams = $sortingRuleParams;
        return $this;
    }

    /**
     * Get the (single) sorting rule that is going to be used for the results.
     *
     * @return SortingRule|null
     * @throws \Exception
     * @since 1.16.0
     */
    public function getCurrent() : ?SortingRule
    {
        if ($this->currentItem === null) {
            $items = $this->getItems();

            /** @var SortingRule $item */
            foreach ($items as $item) {
                if ($item->isActive()) {
                    $current = $item;
                    break;
                }
            }

            // If no active/selected found, set the 1st item as current
            $this->currentItem = $current ?? reset($items);
        }

        return $this->currentItem;
    }

    /**
     * Function to create a default rule when nothing is found
     *
     * @return SortingRule
     * @throws \ReflectionException
     * @since 1.16.0
     */
    protected function getDefault() : SortingRule
    {
        $isSearch = false;
        $app = Factory::getApplication();
        $finderParams = method_exists($app, 'getParams') ? $app->getParams('com_finder') : new Registry();
        if ($app->input->getString('q') || ($finderParams->get('allow_empty_query') && !empty($app->input->get('t')))) {
            $isSearch = true;
        }

        /** @var SortingRule $sortingRuleObj */
        $sortingRuleObj = $this->objectManager->createObject($this->itemObjectClass);

        if ($isSearch) {
            $sortingRuleObj->setSortField(SortingRule::DEFAULT_SORTING_SEARCH_FIELD);
            $sortingRuleObj->setSortDirection(SortingRule::DEFAULT_SORTING_SEARCH_DIR);
        }else {
            $sortingRuleObj->setSortField(SortingRule::DEFAULT_SORTING_FILTERING_FIELD);
            $sortingRuleObj->setSortDirection(SortingRule::DEFAULT_SORTING_FILTERING_DIR);
        }

        return $sortingRuleObj;
    }
}