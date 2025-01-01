<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\AbstractCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Collection\Db;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Section\ValueInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\DynamicFilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered\Nested;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\String\StringHelper;

/**
 * Class Collection
 * @package Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Option
 */
class Collection extends Db
{
    /**
     * @var ValueInterface
     * @since 1.0.0
     */
    protected $optionConfig;

    /**
     * @var string
     * @since 1.0.0
     * @internal
     */
    protected $itemObjectClass = OptionInterface::class;

    /**
     * @var FilterInterface
     * @since 1.0.0
     */
    protected $filter;

    /**
     * @var OptionInterface[]
     * @since 1.2.0
     */
    protected $selected;

    /**
     * @var LoggerInterface
     * @since 1.0.0
     */
    protected $logger;

    /**
     * @var ContextCollection
     * @since 1.0.0
     */
    protected $contextCollection;

    /**
     * @var DynamicConfigCollection
     * @since 1.8.0
     */
    protected $dynamicConfigCollection;

    /**
     * @var bool
     * @since 1.0.0
     */
    protected $joinItemValueTable = true;

    /**
     * Collection constructor.
     *
     * @param   DatabaseInterface        $database
     * @param   ContextCollection        $contextCollection
     * @param   DynamicConfigCollection  $dynamicConfigCollection
     * @param   LoggerInterface          $logger
     * @since 1.0.0
     */
    public function __construct(
        DatabaseInterface $database,
        ContextCollection $contextCollection,
        DynamicConfigCollection $dynamicConfigCollection,
        LoggerInterface $logger
    ) {
        $this->contextCollection = $contextCollection;
        $this->dynamicConfigCollection = $dynamicConfigCollection;
        $this->logger = $logger;
        parent::__construct($database, $logger);
    }

    /**
     * Called at the start of the collection load
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    protected function init(): AbstractCollection
    {
        if ($this->isInitialized === false) {
            $valuesTable = $this->getOptionConfig()->getDbTable();
            $this->setMainTable($valuesTable);
            $valuesItemsTable = $this->getFilterItem()->getConfig()->getValueItemRef()->getDbTable();
            $distinct = false;

            /**
             * the same table is used for storing the values and for assigning them to the items
             * that means that the values are repeated
             */
            if ($valuesTable == $valuesItemsTable) {
                $distinct = true;
            }

            $valueColumn = $this->getOptionConfig()->getValue()->getDbColumn();
            $labelColumn = $this->getOptionConfig()->getLabel()->getDbColumn();

            if($this->getOptionDataType() === 'date' && !$this->getFilterItem()->getAttributes()->get('show_time', 0)) {
                $this->addColumnToSelect('STR_TO_DATE('.self::MAIN_TABLE_ALIAS . '.' . $valueColumn . ', "%Y-%m-%d")', 'value', $distinct);
                $this->addColumnToSelect('STR_TO_DATE('.self::MAIN_TABLE_ALIAS . '.' . $valueColumn . ', "%Y-%m-%d")', 'label');
            } else {
                $this->addColumnToSelect(self::MAIN_TABLE_ALIAS . '.' . $valueColumn, 'value', $distinct);
                $this->addColumnToSelect(self::MAIN_TABLE_ALIAS . '.' . $labelColumn, 'label');
            }

            if($this->getOptionConfig()->getState() && $this->getOptionConfig()->getState()->getDbColumn()) {
                $stateColumn = $this->getOptionConfig()->getState()->getDbColumn();
                $this->addCondition(self::MAIN_TABLE_ALIAS . '.' . $stateColumn, 1);
            }

            if ($this->getOptionConfig()->getAlias() && $this->getOptionConfig()->getAlias()->getDbColumn()) {
                $aliasColumn = $this->getOptionConfig()->getAlias()->getDbColumn();
                $this->addColumnToSelect(self::MAIN_TABLE_ALIAS . '.' . $aliasColumn, 'alias');
            }

            if ($this->getOptionConfig()->getMetadescription() && $this->getOptionConfig()->getMetadescription()->getDbColumn()) {
                $metaDescColumn = $this->getOptionConfig()->getMetadescription()->getDbColumn();
                $this->addColumnToSelect(self::MAIN_TABLE_ALIAS . '.' . $metaDescColumn, 'metadescription');
            }

            if ($this->getOptionConfig()->getMetakeywords() && $this->getOptionConfig()->getMetakeywords()->getDbColumn()) {
                $metaKeyColumn = $this->getOptionConfig()->getMetakeywords()->getDbColumn();
                $this->addColumnToSelect(self::MAIN_TABLE_ALIAS . '.' . $metaKeyColumn, 'metakeywords');
            }
            
            // Set the collection's sorting
            $sortBy = $this->getFilterItem()->getAttributes()->get('options_sort_by', 'label');
            $sortDirection = $this->getFilterItem()->getAttributes()->get('options_sort_direction', 'ASC');
            $sortDirection = strtoupper($sortDirection??'ASC');
            $sortDirection = in_array($sortDirection, ['ASC', 'DESC']) ? $sortDirection : 'ASC';

            // In case of count, sort by 'count' and 'label'
            if($sortBy == 'count') {
                $sortBy = ['count', 'label'];
            }
            $this->setOrderField($sortBy);
            $this->setOrderDir($sortDirection);

        }

        return parent::init();
    }

    /**
     * Always set it before loading the collection
     *
     * @param FilterInterface $filter
     *
     * @return Collection
     * @since 1.0.0
     */
    public function setFilterItem(FilterInterface $filter): Collection
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Return the filter item of that collection
     *
     * @return FilterInterface
     * @since 1.0.0
     */
    public function getFilterItem(): FilterInterface
    {
        if ($this->filter === null) {
            $exception = new \RuntimeException('The filter item is missing from the Option Collection');
            $this->logger->error($exception->getMessage() . ' _ ' . $exception->getFile() . ' (' . $exception->getLine() . ')');
            throw $exception;
        }

        return $this->filter;
    }

    /**
     *  We do not fetch items for range filters (excluding calendars where the actives are shown).
     * This has 2 effects:
     * 1. It renders the filters generation faster.
     * 2. It shows the range filters even if no results are returned.
     * The admin can set display conditions anyway.
     *
     * @since 1.15.0
     * @return array
     */
    public function getItems(): array
    {
        /*
         * In the filters view (modal), we use drop-down lists for any type of filter and any set display type.
         * Hence in that view, we need the filter's items, no matter what.
         */
        $view = Factory::getApplication()->getInput()->get('view');
        if ($view != 'filters' && $this->filter->getIsRange() && $this->filter->getDisplay() != 'calendar') {
            $this->items = [];
        }else {
            parent::getItems();
        }

        return $this->items;
    }

    public function sort($force = false): AbstractCollection
    {
        parent::sort($force);
        $sortBy = $this->getFilterItem()->getAttributes()->get('options_sort_by');

        /*
         * Sort alphabetically does not work properly for numerical values in mySQL queries.
         * Also maybe the labels are translated
         * We are sorting using php in that case.
         */
        if (!$this->getOptionConfig()->getIsTree() && $sortBy == 'label' && !$this->filter->getIsRange()) {
            $startsNumerical = false;
            $items = $this->getItems();

            $sortDirection = strtoupper($this->getFilterItem()->getAttributes()->get('options_sort_direction'));
            $sortDirection = in_array($sortDirection, ['ASC', 'DESC']) ? $sortDirection : 'ASC';
            $sortDirection = $sortDirection == 'ASC' ? SORT_ASC : SORT_DESC;

            if ($sortDirection == SORT_ASC) {
                $fn = function ($a, $b) {
                    $startsNumericalItem = ctype_digit(substr($a->getLabel(), 0, 1)) || ctype_digit(substr($b->getLabel(), 0, 1));
                    if ($startsNumericalItem) {
                        return strnatcmp($a->getLabel(), $b->getLabel());
                    } else {
                        return StringHelper::strcasecmp($a->getLabel(), $b->getLabel());
                    }
                };
            } else {
                $fn = function ($a, $b) {
                    $startsNumericalItem = ctype_digit(substr($a->getLabel(), 0, 1)) || ctype_digit(substr($b->getLabel(), 0, 1));
                    if ($startsNumericalItem) {
                        return strnatcmp($b->getLabel(), $a->getLabel());
                    } else {
                        return StringHelper::strcasecmp($b->getLabel(), $a->getLabel());
                    }
                };
            }

            usort($items, $fn);
            $this->items = $items;
        }

        return $this;
    }

    /**
     * Join or not the itemValueRef table.
     * It is used when counter is enabled in the options.
     *
     * @param bool $join
     *
     * @return Collection
     * @since 1.0.0
     */
    public function setJoinItemValueTable(bool $join): Collection
    {
        $this->joinItemValueTable = $join;
        return $this;
    }

    /**
     * Function that adds the language condition for the options
     *
     * @param array $languages
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    public function addLanguageCondition(array $languages): AbstractCollection
    {
        if ($languages && $this->getFilterItem()->getConfig()->getValue()->getLanguage() && $languageColumn = $this->getFilterItem()->getConfig()->getValue()->getLanguage()->getDbColumn()) {
            $this->addCondition(self::MAIN_TABLE_ALIAS . '.' . $languageColumn, $languages, 'IN');
        }

        return $this;
    }

    /**
     * Get the configuration for the option
     *
     * @return ValueInterface
     * @since 1.0.0
     */
    public function getOptionConfig(): ValueInterface
    {
        if ($this->optionConfig === null) {
            $this->optionConfig = $this->getFilterItem()->getConfig()->getValue();
        }

        return $this->optionConfig;
    }

    /**
     * Get the data type of that Collection Option
     *
     * @return string
     * @since 1.8.0
     */
    public function getOptionDataType(): string
    {
        $filter = $this->getFilterItem();
        $dataType = $filter->getAttributes()->get('dataType', '');

        if (!$dataType && $filter instanceof DynamicFilterInterface && $filter->getType()) {
            $dynamicConfigFilter = $this->dynamicConfigCollection->getByNameAttribute($filter->getType());
            // Not all the dynamic filters referenced in the dynamic.xml
            $dataType = $dynamicConfigFilter ? $dynamicConfigFilter->getDataType() : '';
        }

        // Get it from the filters.xml
        if (empty($dataType)) {
            $dataType = $this->getOptionConfig()->getValue()->getType() ?? 'string';
        }

        return $dataType;
    }

    /**
     * Get the selected options of this collection
     *
     * @return OptionInterface[]
     * @throws \ReflectionException
     * @since 1.2.0
     */
    public function getSelected()
    {
        if ($this->selected === null) {
            $this->selected = [];
            $this->getItems();
            $collection = $this;

            // For nested options get the entire tree.
            if ($this->getOptionConfig()->getIsTree() && $this instanceof Nested) {
                $collection = $this->getNonNestedOptions();
            }
            /** @var OptionInterface $item */
            foreach ($collection->items as $item) {
                if ($item->isSelected()) {
                    $this->selected[] = $item;
                }
            }
        }

        return $this->selected;
    }

    /**
     * @return Db
     * @throws \Exception
     * @since 1.0.0
     */
    protected function beforeLoad(): Db
    {
        if ($this->getFilterItem()->getConfig()->isDynamic()) {
            if (empty($this->getFilterItem()->getParentId())) {
                $exception = new \RuntimeException('Parent id is missing from the filter with name: ' . $this->getFilterItem()->getName());
                $this->logger->error($exception->getMessage() . ' _ ' . $exception->getFile() . ' (' . $exception->getLine() . ')');
                throw $exception;
            }
            //@todo check if that option's parent id column, is the same as the id column in the definition section
            $this->addCondition(self::MAIN_TABLE_ALIAS . '.' . $this->getOptionConfig()->getParentId()->getDbColumn(), $this->getFilterItem()->getParentId());
        }

        /*
         * An option (e.g. category) can be related with several extension (e.g. com_content and com_contacts).
         * This happens when the collection contains options from several extensions
        */
        if ($this->getOptionConfig()->getExtension() !== null && $this->getOptionConfig()->getExtension()->getDbColumn()) {
            // Can be context of type com_something.item. Extract only the extension part.
            $extension = substr($this->getFilterItem()->getContext(), 0, strpos($this->getFilterItem()->getContext(), '.'));

            $this->addCondition(self::MAIN_TABLE_ALIAS . '.' . $this->getOptionConfig()->getExtension()->getDbColumn(), $extension , '=', 'OR', 'extension');
            $this->addCondition(self::MAIN_TABLE_ALIAS . '.' . $this->getOptionConfig()->getExtension()->getDbColumn(), 'system' , '=', 'OR', 'extension');
        }

        $this->joinItemValueTable();

        return parent::beforeLoad();
    }

    /**
     * @return $this
     * @throws \Exception
     * @since 2.0.0
     */
    protected function joinItemValueTable() : self
    {
        if ($this->joinItemValueTable) {
            $query = $this->getQuery();
            $valueItemRefConfiguration = $this->getFilterItem()->getConfig()->getValueItemRef();
            $ValueItemTableAlias = $this->getItemValueRefTableAlias();
            $contextTableAlias = $this->getContextTableAlias();
            $contextConfig = $this->contextCollection->getByNameAttribute($this->getFilterItem()->getContext());

            /**
             * A different table is used for storing values and the value>item ref
             */
            if (self::MAIN_TABLE_ALIAS != $ValueItemTableAlias) {
                /*
                 * In the join the left side is the reference field's db column.
                 * The right side is the field's db column itself.
                 *
                 * A single field from the filters.xml gives the entire relationship and condition
                 */
                $this->join([
                    $ValueItemTableAlias => $valueItemRefConfiguration->getDbTable()
                ],
                    self::MAIN_TABLE_ALIAS . '.' . $valueItemRefConfiguration->getValueId()->getReference()->getDbColumn() . '=' .
                    $ValueItemTableAlias . '.' . $valueItemRefConfiguration->getValueId()->getDbColumn());
            }

            // the items/context table is not referenced to the query
            if ($contextTableAlias != $ValueItemTableAlias) {

                $this->join([
                    $contextTableAlias => $contextConfig->getItem()->getDbTable()
                ],
                    $ValueItemTableAlias . '.' . $valueItemRefConfiguration->getItemId()->getDbColumn() . '=' .
                    $contextTableAlias . '.' . $contextConfig->getItem()->getId()->getDbColumn());
            }

            // there is a reference to specific typeIds (i.e. contexts) in that table
            if ($valueItemRefConfiguration->getTypeId() && $valueItemRefConfiguration->getTypeId()->getDbColumn() && $contextConfig->getTypeId()) {
                $this->addCondition($ValueItemTableAlias . '.' . $valueItemRefConfiguration->getTypeId()->getDbColumn(), $contextConfig->getTypeId());
            }

            /*
             * Load also the counter
             */
            $this->addColumnToSelect('count(DISTINCT(' . $contextTableAlias . '.' . $this->database->quoteName($contextConfig->getItem()->getId()->getDbColumn()) . '))', 'count');

            /*
             * Set conditions that may apply to items
             */
            if ($contextConfig->getItem()->getState() !== null) {
                $this->addCondition($contextTableAlias . '.' . $contextConfig->getItem()->getState()->getDbColumn(), '1');
            }

            /*
             * Condition by publish_start_date
             */
            if ($contextConfig->getItem()->getPublishStartDate() !== null) {
                // Get the current date, minus seconds.
                $nowDate = substr_replace(Factory::getDate()->toSql(), '00', -2);
                $this->addCondition($contextTableAlias . '.' . $contextConfig->getItem()->getPublishStartDate()->getDbColumn(), '', 'IS NULL', 'OR', 'publish_start_date');
                $this->addCondition($contextTableAlias . '.' . $contextConfig->getItem()->getPublishStartDate()->getDbColumn(), $nowDate, '<=', 'OR', 'publish_start_date');
            }

            /*
             * Condition by publish_end_date
             */
            if ($contextConfig->getItem()->getPublishEndDate() !== null) {
                // Get the current date, minus seconds.
                $nowDate = substr_replace(Factory::getDate()->toSql(), '00', -2);
                $this->addCondition($contextTableAlias . '.' . $contextConfig->getItem()->getPublishEndDate()->getDbColumn(), '', 'IS NULL', 'OR', 'publish_end_date');
                $this->addCondition($contextTableAlias . '.' . $contextConfig->getItem()->getPublishEndDate()->getDbColumn(), $nowDate, '>=', 'OR', 'publish_end_date');
            }

            if ($contextConfig->getItem()->getAccess() !== null) {
                try {
                    $user = Factory::getApplication()->getIdentity();
                    if ($user) {
                        $userGroups = array_unique($user->getAuthorisedViewLevels());
                        $this->addCondition($contextTableAlias . '.' . $contextConfig->getItem()->getAccess()->getDbColumn(),
                            $userGroups, 'IN');
                    }
                } catch (\Exception $exception) {
                    //suck it. Exception is thrown in unit tests because the application does not exist.
                }
            }

            $multiLanguage = false;
            try {
                $multiLanguage = Multilanguage::isEnabled();
            } catch (\Exception $exception) {
                //suck it. Application is missing in unit tests.
            }

            if ($multiLanguage && $contextConfig->getItem()->getLanguage() !== null) {
                $this->addCondition($contextTableAlias . '.' . $contextConfig->getItem()->getLanguage()->getDbColumn(),
                    [
                        Factory::getApplication()->getLanguage()->getTag(),
                        '*'
                    ], 'IN');
            }

            $groupByColumn = self::MAIN_TABLE_ALIAS . '.' . $this->getFilterItem()->getConfig()->getValue()->getValue()->getDbColumn();
            
            // When we are dealing with dates, GROUP BY `label`, fixes the issue with the wrong counter
            if($this->getOptionDataType() === 'date') {
                $groupByColumn = 'label';
            }

            $query->group($this->database->quoteName($groupByColumn));
        }

        return $this;
    }

    /**
     * Get the alias of the ValueItemXref table
     *
     * This table can be the same as the main table, if no separate table is used for storing that relationship.
     *
     * @return string
     * @since 1.0.0
     */
    protected function getItemValueRefTableAlias(): string
    {
        $valueTable = $this->getFilterItem()->getConfig()->getValue()->getDbTable();
        $itemValueTable = $this->getFilterItem()->getConfig()->getValueItemRef()->getDbTable();
        $alias = self::MAIN_TABLE_ALIAS;

        /**
         * A different table is used for storing the value>item ref
         */
        if ($valueTable != $itemValueTable) {
            $alias = 'itemValueRef';
        }

        return $alias;
    }

    /**
     * Get the alias of the context/items table
     *
     * @return string
     * @since 1.0.0
     */
    protected function getContextTableAlias(): string
    {
        $contextConfig = $this->contextCollection->getByNameAttribute($this->getFilterItem()->getContext());
        $contextDbTable = $contextConfig->getItem()->getDbTable();
        $alias = $this->getItemValueRefTableAlias();
        if ($contextDbTable != $this->getFilterItem()->getConfig()->getValueItemRef()->getDbTable()) {
            $alias = 'items';
        }

        return $alias;

    }

    /**
     * Set the parent filter as property to each Option item
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    protected function afterLoad(): AbstractCollection
    {
        /** @var OptionInterface $item */
        foreach ($this as $item) {
            $item->setParentFilter($this->getFilterItem());
        }

        $collection = parent::afterLoad();

        /*
         * Allow 3rd party plugins to make changes (e.g. ACF - Countries).
         * Mainly we want them to change the labels, but we do not have any safeguard for that.
         */
        if (in_array($this->getFilterItem()->getConfigName(), ['fields', 'fields/subform'])) {
            $dispatcher = Factory::getContainer()->get(DispatcherInterface::class);

            // Process the field plugins.
            PluginHelper::importPlugin('fields', null, true, $dispatcher);
            $dispatcher->dispatch('onJFiltersOptionsAfterCreation', new AbstractImmutableEvent('onJFiltersOptionsAfterCreation', ['collection' => $collection]));
        }

        return $collection;
    }
}
