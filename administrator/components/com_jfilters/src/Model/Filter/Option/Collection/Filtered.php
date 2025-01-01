<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Collection\Db;
use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\ValueItemRefInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as filterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Site\Model\ResultsModel;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseInterface;

/**
 * Class Filtered
 *
 * The class filters the options based on the selections in the other filters.
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection
 * @since 1.0.0
 */
class Filtered extends Collection implements FilteredInterface
{
    /**
     * @var filterCollection
     * @since 1.0.0
     */
    protected $filterCollection;

    /**
     * @var ValueItemRefInterface
     * @since 1.0.0
     */
    protected $optionItemRefConfig;

    /**
     * @var CMSApplicationInterface
     * @since 1.0.0
     */
    protected $application;

    /**
     * @var ComponentConfig
     * @since 1.0.0
     */
    protected $componentConfig;

    /**
     * @var ResultsModel
     * @since 1.0.0
     */
    protected $componentModel;

    /**
     * Whether, the selections in the other filters will be taken into account.
     *
     * @var bool
     * @since 1.0.0
     */
    protected $useOtherFiltersAsConditions = true;

    /**
     * Whether, we have a search query.
     *
     * @var bool
     * @since 1.0.0
     */
    protected $hasValidQueryCondition = false;

    /**
     * Filtered constructor.
     *
     * @param   DatabaseInterface        $database
     * @param   ContextCollection        $contextCollection
     * @param   DynamicConfigCollection  $dynamicConfigCollection
     * @param   LoggerInterface          $logger
     * @param   filterCollection         $filterCollection
     * @param   CMSApplicationInterface  $application
     * @param   ComponentConfig          $componentConfig
     * @since 1.0.0
     */
    public function __construct(
        DatabaseInterface $database,
        ContextCollection $contextCollection,
        DynamicConfigCollection $dynamicConfigCollection,
        LoggerInterface $logger,
        filterCollection $filterCollection,
        CMSApplicationInterface $application,
        ComponentConfig $componentConfig
    ) {
        $this->filterCollection = $filterCollection;
        $this->application = $application;
        $this->componentConfig = $componentConfig;
        parent::__construct($database, $contextCollection, $dynamicConfigCollection, $logger);
    }

    public function setUseOtherSelectionsAsConditions(bool $use): bool
    {
        $clearCollection = false;

        // If the filters selection change the collection items, it should be reloaded
        if ($this->isLoaded && $use != $this->useOtherFiltersAsConditions && ($this->hasValidQueryCondition || !$this->getFilterItem()->getRoot())) {
            /*
             * Do not call $this->filterCollection->getSelectedItems() without making sure that this collection is already loaded.
             * The collection is loaded initially by the router, where no input exists.
             */
            $filtersWithSelection = $this->filterCollection->getSelectedItems();
            if(count($filtersWithSelection) > 1 || (count($filtersWithSelection) == 1 && reset($filtersWithSelection) != $this->getFilterItem())) {
                $this->clearItems();
                $clearCollection = true;
            }
        }

        $this->useOtherFiltersAsConditions = $use;

        return $clearCollection;
    }

    public function setQueryCondition(?string $context): FilteredInterface
    {
        $key = ResultsModel::class;
        if ($this->componentModel === null && $this->objectManager->getContainer()->has($key)) {
            $this->componentModel = $this->objectManager->getContainer()->get($key);
        }

        // It can be stored as false in the container. Hence check again and boot the component.
        if (!$this->componentModel) {
            /** @var ResultsModel componentModel */
            $this->componentModel = $this->application->bootComponent('com_jfilters')->getMVCFactory()->createModel('Results',
                'Site',
                ['ignore_filters_request' => true]);

            /**
             * Set it to the container for reuse.
             * This costs in performance as it queries the database again.
             * @see \Joomla\Component\Finder\Site\Model\SearchModel::populateState()
             */
            $this->objectManager->getContainer()->set($key, $this->componentModel, true);
        }

        $this->componentModel->setState('context', $context);
        $ids = $this->componentModel->getItemIdsFromSearch();

        if (!empty($ids)) {
            $dbColumn = $this->getFilterItem()->getConfig()->getValueItemRef()->getItemId()->getDbColumn();
            $this->addCondition($this->getItemValueRefTableAlias() . '.' . $dbColumn, $ids, 'IN');
            $this->hasValidQueryCondition = true;
        } // No ids,means null options for that filter
        else {
            $this->clearItems(false);
            $this->isLoaded = true;
        }

        return $this;
    }

    /**
     * Sets the selections of the other filters as conditions, to the options of that filter.
     *
     * @return Filtered
     * @throws \Exception
     * @since 1.0.0
     */
    protected function beforeLoad(): Db
    {
        // we call that at the start, since it formats the query (e.g. joins) properly.
        parent::beforeLoad();

        $currentFilter = $this->getFilterItem();

        // root filters are not affected. Also, this can be set through a param ($useOtherFiltersAsConditions), e.g based on the filter's settings.
        if ($currentFilter->getRoot() || $this->useOtherFiltersAsConditions === false) {
            return $this;
        }

        /** @var FilterInterface $filter */
        foreach ($this->filterCollection as $filter) {
            // It is the filter that this collection belongs to. Cannot be used for cross-checking.
            if ($currentFilter->getId() == $filter->getId()) {
                continue;
            }
            if (!empty($filter->getRequest())) {
                $currentValueRefTableAlias = $this->getItemValueRefTableAlias();
                $currentValueRefItemIdColumn = $this->getOptionItemRefConfig()->getItemId()->getDbColumn();
                $this->getOptionConfig()->getValue()->getDbColumn();

                $alias = FilterInterface::URL_FILTER_VAR_NAME . $filter->getId();
                $joinValueRefTable = $filter->getConfig()->getValueItemRef()->getDbTable();
                $joinTableItemIdColumn = $filter->getConfig()->getValueItemRef()->getItemId()->getDbColumn();
                $joinTableValueIdColumn = $filter->getConfig()->getValueItemRef()->getValueId()->getDbColumn();

                $this->join(
                    [$alias => $joinValueRefTable],
                    $currentValueRefTableAlias . '.' . $currentValueRefItemIdColumn . '=' . $alias . '.' . $joinTableItemIdColumn
                );

                $dataType = $filter->getOptions()->getOptionDataType();
                // Check if we should use WHERE IN or WHERE LIKE or WHERE BETWEEN or <= >=
                $operator = '';

                if ($filter->getIsRange()) {
                    $operator = 'BETWEEN';
                }
                elseif ($dataType === 'date' && !$filter->getAttributes()->get('show_time', 0)) {
                    $operator = 'LIKE';
                }
                if (empty($operator)) {
                    foreach ($filter->getRequest() as $requestValue) {
                        // If it is equal to the max length, probably is cut, or if it's a date without time, use WHERE LIKE
                        if (!is_numeric($requestValue) && mb_strlen($requestValue) == $this->componentConfig->get('max_option_value_length',
                                35)) {
                            $operator = 'LIKE';
                        }
                    }
                }
                if (empty($operator)) {
                    $operator = 'IN';
                }

                // Add a condition that references the parent_id (e.g. field id) for value tables using values from multiple fields (e.g. custom fields).
                if ($joinValueRefTable == $filter->getConfig()->getValue()->getDbTable() && $filter->getConfig()->getValue()->getParentId()) {
                    $this->addCondition($alias . '.' . $filter->getConfig()->getValue()->getParentId()->getDbColumn(),
                        $filter->getParentId());
                }

                $queryGlue = 'AND';

                // Whether the parent node returns the sub-nodes results.
                $subNodesOnParent = $filter->getConfig()->getValue()->getIsTree() && $filter->getAttributes()->get('show_sub_node_contents_on_parent',0) ? true : false;

                if ($subNodesOnParent && $filter->getConfig()->getValue()->getLft() && $filter->getConfig()->getValue()->getRgt()) {
                    $valuesTable = $filter->getConfig()->getValue()->getDbTable();
                    $lftDbColumn = $filter->getConfig()->getValue()->getLft()->getDbColumn();
                    $rgtDbColumn = $filter->getConfig()->getValue()->getRgt()->getDbColumn();
                    $queryGlue = 'OR';

                    /*
                     * This is a cacophony.
                     * We do not want sql code in our collections.
                     * But given its complexity, it requires deep refactoring to make it sql agnostic, for sub queries as conditions.
                     */
                    $query = $this->database->getQuery(true);
                    $query->select('sub.id')
                        ->from($this->database->quoteName($valuesTable, 'sub'))
                        ->join('INNER',
                            $this->database->quoteName($valuesTable, 'this'),
                            $this->database->quoteName('sub.'.$lftDbColumn) . '>' . $this->database->quoteName('this.'.$lftDbColumn) . ' AND ' .
                            $this->database->quoteName('sub.'.$rgtDbColumn) . '<' . $this->database->quoteName('this.'.$rgtDbColumn)
                        )->where($this->database->quoteName('this.' . $filter->getConfig()->getValue()->getValue()->getDbColumn()) . 'IN('. implode(',', $filter->getRequest()) . ')');

                    $this->addCondition($alias . '.' . $joinTableValueIdColumn, $query, 'IN', 'OR', $filter->getId());
                }

                $requestValues = $filter->getRequest();

                // Ranges
                if ($filter->getIsRange()) {
                    if (isset($requestValues[1]) && $dataType == 'date' && !$filter->getAttributes()->get('show_time', 0)) {
                        // We need to set the biggest possible time for the 2nd date
                        $requestValues[1] = $requestValues[1] . ' 23:59:59';
                    }
                    // Min range
                    if (isset($requestValues[0])) {
                        $this->addCondition($alias . '.' . $joinTableValueIdColumn, $requestValues[0], '>=', 'AND', $filter->getId());
                    }
                    // Max Range
                    if (isset($requestValues[1])) {
                        $this->addCondition($alias . '.' . $joinTableValueIdColumn, $requestValues[1], '<=', 'AND', $filter->getId());
                    }
                }
                else {
                    $this->addCondition($alias . '.' . $joinTableValueIdColumn, $requestValues, $operator, $queryGlue, $filter->getId());
                }
            }
        }

        return $this;
    }

    /**
     * Gets the configuration for the itemRef
     *
     * @return ValueItemRefInterface
     * @since 1.0.0
     */
    protected function getOptionItemRefConfig(): ValueItemRefInterface
    {
        if ($this->optionItemRefConfig === null) {
            $this->optionItemRefConfig = $this->getFilterItem()->getConfig()->getValueItemRef();
        }

        return $this->optionItemRefConfig;
    }

}
