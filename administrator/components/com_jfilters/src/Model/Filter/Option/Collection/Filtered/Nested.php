<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2020 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Helper\OptionsHelper;
use Bluecoder\Component\Jfilters\Administrator\Model\AbstractCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Collection\Db;
use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Dynamic\Collection as DynamicConfigCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as filterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered as OptionCollectionFiltered;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\FilteredInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\CollectionFactory;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\NestedInterface as NestedOptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\DatabaseInterface;

class Nested extends OptionCollectionFiltered
{
    /**
     * @internal
     * @var string
     */
    protected $itemObjectClass = NestedOptionInterface::class;

    /** @var Nested */
    protected $parentNodesCollection;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $newParentNodes = [];

    /**
     * @var array
     * @since 1.0.0
     */
    protected $itemsAssocArray;

    /**
     * @var Nested
     * @since 1.0.0
     */
    protected $nonNestedOptions;

    /**
     * @var CollectionFactory
     * @since 1.0.0
     */
    protected $collectionFactory;

    /**
     * @var bool
     * @since 1.0.0
     */
    protected $nestingProcessed = false;

    /**
     * Nested constructor.
     *
     * @param   DatabaseInterface        $database
     * @param   ContextCollection        $contextCollection
     * @param   LoggerInterface          $logger
     * @param   filterCollection         $filterCollection
     * @param   CMSApplicationInterface  $application
     * @param   ComponentConfig          $componentConfig
     * @param   CollectionFactory        $collectionFactory
     */
    public function __construct(
        DatabaseInterface $database,
        ContextCollection $contextCollection,
        DynamicConfigCollection $dynamicConfigCollection,
        LoggerInterface $logger,
        filterCollection $filterCollection,
        CMSApplicationInterface $application,
        ComponentConfig $componentConfig,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($database, $contextCollection, $dynamicConfigCollection, $logger, $filterCollection, $application, $componentConfig);
    }

    public function setUseOtherSelectionsAsConditions(bool $use): bool
    {
        $clearCollection = parent::setUseOtherSelectionsAsConditions($use);

        // If the filters selection change the collection items, it should be reloaded
        if ($clearCollection) {
            $this->clearNesting();
        }

        return $clearCollection;
    }

    public function setQueryCondition(?string $context): FilteredInterface
    {
        parent::setQueryCondition($context);
        if ($this->hasValidQueryCondition) {
            $this->clearNesting();
        }

        return $this;
    }

    /**
     * Clear the nesting when the collection is cleared
     *
     * @since 1.0.0
     */
    protected function clearNesting()
    {
        $this->nestingProcessed = false;
        $this->nonNestedOptions = null;
        $this->itemsAssocArray = null;
        $this->parentNodesCollection = null;
        $this->newParentNodes = [];
    }

    /**
     * Returns the opions collection non nested.
     *
     * @return Nested
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function getNonNestedOptions()
    {
        // create a Nested Collection without the applied nesting. Just plain Options
        if($this->nonNestedOptions === null) {
            $this->createNested();
            $optionsHelper = OptionsHelper::getInstance();
            $this->nonNestedOptions = $optionsHelper->getFullTree($this, 0, '');
        }
        return $this->nonNestedOptions;
    }

    /**
     * Allows us to set non-nested options without the need to re-build the tree.
     * This is useful when we use sub-tree collections from a nested collection.
     *
     * @param   Nested  $collection
     *
     * @return $this
     * @since 1.5.6
     */
    public function setNonNestedOptions(Nested $collection): Nested
    {
        $this->nonNestedOptions = $collection;

        return $this;
    }

    /**
     * Creates a nested tree of the options
     *
     * @return $this
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function createNested(): Nested
    {
        if ($this->nestingProcessed === false) {
            $reExecuteNesting = false;
            $rootId = $this->getFilterItem()->getAttributes()->get('root_option', 1);
            $mappedItemsTmp = $this->getItemsAssocArray();

            /**
             * Then use that assoc. array, to set the parent Option for each element.
             */

            /** @var NestedOptionInterface $item */
            foreach ($this as $item) {

                if (!empty($item->getParentId()) && isset($mappedItemsTmp[$item->getParentId()]) && $item->getParentId() > 1 && $item->getValue() != $rootId) {
                    /** @var NestedOptionInterface $parentOption */
                    $parentOption = $mappedItemsTmp[$item->getParentId()];
                    $item->setParentOption($parentOption);
                    $childrenCollection = $parentOption->getChildren();

                    // There is no children collection. Set it
                    if ($childrenCollection === null) {
                        /*
                         * We add the params to the factory, for the unit tests.
                         * Otherwise php unit instantiates them again and throws an error in the
                         * application instantiation.
                         */
                        $childrenCollection = $this->collectionFactory->create(Nested::class,
                                                                               [
                                                                                   $this->database,
                                                                                   $this->contextCollection,
                                                                                   $this->dynamicConfigCollection,
                                                                                   $this->logger,
                                                                                   $this->filterCollection,
                                                                                   $this->application,
                                                                                   $this->componentConfig,
                                                                                   $this->collectionFactory
                                                                               ]
                        );
                        $childrenCollection->setFilterItem($this->getFilterItem());
                        $childrenCollection->conditionGroups = $this->conditionGroups;
                        $parentOption->setChildren($childrenCollection);
                    }

                    if ($childrenCollection->isLoaded === false ||
                        ($childrenCollection->isLoaded && $childrenCollection->getByAttribute('value', $item->getValue()) === null)) {
                        /** @var CollectionFiltered $childrenCollection */
                        $childrenCollection->addItem($item);
                    }

                    // remove the item from the current collection, is now in $childrenCollection
                    $this->remove($item);

                    // the $parentOption does not exist in the collection. Add it.
                    if (!isset($this->newParentNodes[$parentOption->getValue()])) {
                        if ($this->getByAttribute('value', $parentOption->getValue()) === null) {
                            $this->addItem($parentOption);
                        }
                        $this->newParentNodes [$parentOption->getValue()] = $parentOption;
                    }

                    // check if the parent has a higher parent, hence further nesting
                    if ($parentOption->getParentId() > 1 && $parentOption->getValue() != $rootId) {
                        $reExecuteNesting = true;
                    }
                } else {
                    // The only reason we do that is for keeping the proper order.
                    $this->remove($item);
                    $this->addItem($item);
                }
            }

            if ($reExecuteNesting) {
                $this->createNested();
            }
            // make the keys linear
            $this->items = array_values($this->items);
            $this->nestingProcessed = true;
        }

        return $this;
    }

    /**
     * Set the hasChildSelected boolean property to the parent nodes.
     *
     * We do not add that logic during the tree creation, because the collection is used by the router,
     * where the application's Input is not yet set, hence we can cannot get the selected options.
     *
     * @param   Nested|null  $collection
     *
     * @return Nested
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function setHasChildSelectedToParent(?Nested $collection = null): Nested
    {
        $collection = $collection ?: $this;

        // No nesting create it.
        if ($collection->getItems() && $collection->nestingProcessed === false) {
            $this->createNested();
        }
        /** @var NestedOptionInterface $option */
        foreach ($collection as $option) {
            if ($option->isSelected() && $option->getParentOption() && !$option->getParentOption()->hasChildSelected()) {
                $optionTmp = $option;
                while ($optionTmp->getParentOption()) {
                    $optionTmp->getParentOption()->setHasChildSelected(true);
                    // propagate up
                    $optionTmp = $optionTmp->getParentOption();
                }
            }
            if ($option->getChildren()) {
                $this->setHasChildSelectedToParent($option->getChildren());
            }
        }

        return $this;
    }

    /**
     * Creates an assoc. array of the Options using as key the value (id).
     *
     * @return array|null
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function getItemsAssocArray()
    {
        if ($this->itemsAssocArray === null) {
            $mappedItemsTmp = [];
            $items = $this->getItems();
            if ($parentNodesCollectionTmp = $this->getParentNodesCollection()) {
                $items = array_merge($items, $parentNodesCollectionTmp->getItems());
            }
            /*
             * Create an assoc. array with the element ids (values).
             */
            /** @var  OptionInterface $mappedItem */
            foreach ($items as $mappedItem) {
                if (!isset($mappedItemsTmp[$mappedItem->getValue()])) {
                    $mappedItemsTmp[$mappedItem->getValue()] = $mappedItem;
                }
            }
            $this->itemsAssocArray = $mappedItemsTmp;
        }

        return $this->itemsAssocArray;
    }

    /**
     * Return all the parent nodes of a filter
     *
     * @return false|Nested
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function getParentNodesCollection()
    {
        if ($this->parentNodesCollection === null) {
            $filter = $this->getFilterItem();
            $this->parentNodesCollection = false;
            /** @var Field $parentValueId */
            $parentValueId = $filter->getConfig()->getValue()->getParentValueId();
            if ($parentValueId !== null) {
                if (empty($parentValueId->getDbColumn())) {
                    return $this->parentNodesCollection;
                }

                /**
                 * We add the params to the factory, for the unit tests.
                 * Otherwise php unit instantiates them again and throws an error in the
                 * application instantiation.
                 *
                 * @var Nested $optionCollection
                 */
                $this->parentNodesCollection = $this->collectionFactory->create(Nested::class,
                                                                                [
                                                                                    $this->database,
                                                                                    $this->contextCollection,
                                                                                    $this->dynamicConfigCollection,
                                                                                    $this->logger,
                                                                                    $this->filterCollection,
                                                                                    $this->application,
                                                                                    $this->componentConfig,
                                                                                    $this->collectionFactory
                                                                                ]
                );
                $this->parentNodesCollection->setUseOtherSelectionsAsConditions(false);
                // avoid endless recursion (see $this->afterLoad())
                $this->parentNodesCollection->nestingProcessed = true;
                $this->parentNodesCollection->setFilterItem($filter);
                $this->parentNodesCollection->addColumnToSelect(self::MAIN_TABLE_ALIAS . '.' . $this->getOptionConfig()->getValue()->getDbColumn(), 'value', true);
                $this->parentNodesCollection->addColumnToSelect(self::MAIN_TABLE_ALIAS . '.lft');
                $this->parentNodesCollection->join(['joinTable' => $filter->getConfig()->getValue()->getDbTable()],
                                                   self::MAIN_TABLE_ALIAS . '.' . $filter->getConfig()->getValue()->getValue()->getDbColumn() . '=' . 'joinTable.' . $parentValueId->getDbColumn());

                $this->parentNodesCollection->addCondition('joinTable.' . $parentValueId->getDbColumn(), '0', '>');
                $this->parentNodesCollection->setOrderField(self::MAIN_TABLE_ALIAS . '.' . $filter->getConfig()->getValue()->getLft()->getDbColumn());
                $this->parentNodesCollection->setOrderDir('ASC');

                $currentIds = [];
                /** @var OptionInterface $item */
                foreach ($this as $item) {
                    $currentIds[] = $item->getValue();
                }

                // Do not return the same items, we already have.
                if (!empty($currentIds)) {
                    $this->parentNodesCollection->addCondition(
                        'joinTable.' . $parentValueId->getDbColumn(),
                        $currentIds,
                        'NOT IN'
                    );
                }

                // Add language condition
                // Use the language of the filter to show options (e.g. Categories) in the backend of JFilters
                if (Factory::getApplication()->isClient('administrator') && Factory::getApplication()->getInput()->getCmd('option', '') == 'com_jfilters') {
                    $filterLanguage = $this->getFilterItem()->getLanguage();
                    $languages = ['*'];
                    if ($filterLanguage !== '*') {
                        $languages[] = $filterLanguage;
                    }
                } else {
                    $languages = [
                        Factory::getApplication()->getLanguage()->getTag(),
                        '*'
                    ];
                }
                if (!empty($language) && Multilanguage::isEnabled()) {
                    $this->parentNodesCollection->addLanguageCondition($languages);
                }
                $this->parentNodesCollection->setJoinItemValueTable(false);
            }
        }

        return $this->parentNodesCollection;
    }

    /**
     * @return Db
     * @throws \Exception
     * @since 1.0.0
     */
    protected function beforeLoad(): Db
    {
        $filterValueConfig = $this->getFilterItem()->getConfig()->getValue();

        // Missing required config fields. Throw exception
        if ($filterValueConfig->getParentValueId() === null || $filterValueConfig->getLft() === null || $filterValueConfig->getRgt() === null) {

            $missingConfigFields = [];
            if($filterValueConfig->getParentValueId() === null) {
                $missingConfigFields[]= 'Parent Id';
            }
            if($filterValueConfig->getLft() === null) {
                $missingConfigFields[]= 'lft';
            }
            if($filterValueConfig->getRgt() === null) {
                $missingConfigFields[]= 'rgt';
            }

            $exception = new \RuntimeException(sprintf('No "' .implode(', ', $missingConfigFields). '" fields, defined for the filter %s',
                                                   $this->getFilterItem()->getName()));
            $this->logger->critical($exception);
            throw $exception;
        }

        $this->addColumnToSelect(self::MAIN_TABLE_ALIAS . '.' . $this->getFilterItem()->getConfig()->getValue()->getParentValueId()->getDbColumn(),
            'parent_id');

        $valueColumn = $this->escapeName($filterValueConfig->getValue()->getDbColumn());
        $lftDbColumn = $this->escapeName($filterValueConfig->getLft()->getDbColumn());
        $rgtDbColumn = $this->escapeName($filterValueConfig->getRgt()->getDbColumn());
        $mainTable = $this->escapeName(self::MAIN_TABLE_ALIAS);

        // This join lets us get sub-trees though a parent id
        $this->join(
            ['tree' => $this->getMainTable()], '(' . $mainTable . '.' . $lftDbColumn . ' <= `tree`.' . $lftDbColumn . ' AND
            `tree`. ' . $lftDbColumn . ' < ' . $mainTable . '.' . $rgtDbColumn . ') OR 
            (`tree`.`lft` < ' . $mainTable . '.' . $lftDbColumn . ' AND ' . $mainTable . '.' . $rgtDbColumn . ' < `tree`.' . $rgtDbColumn .')'
        );

        // Unit tests need that. Otherwise null is returned
        $parentId = $this->getFilterItem()->getAttributes()->get('root_option', 1) ?? 1;
        $this->addCondition('tree.' . $this->getOptionConfig()->getValue()->getDbColumn(), $parentId);

        $showSubNodeContentLevel = $this->getFilterItem()->getAttributes()->get('show_sub_node_contents_on_parent', 0);

        if ($showSubNodeContentLevel && $this->joinItemValueTable) {
            $this->join(
                ['tree2' => $this->getMainTable()], '(' .
                $mainTable . ' . ' . $lftDbColumn . ' <= `tree2`.' . $lftDbColumn . ' AND ' .
                $mainTable . ' . ' . $rgtDbColumn . ' >= `tree2`.' . $rgtDbColumn .
                ')'
            );

            $this->addCondition('tree2.' . $this->getOptionConfig()->getState()->getDbColumn(), 1);
            $this->addCondition(self::MAIN_TABLE_ALIAS . '.' . $filterValueConfig->getValue()->getDbColumn(), 1 , '>');
        }

        $this->setOrderField(self::MAIN_TABLE_ALIAS . '.' . $filterValueConfig->getLft()->getDbColumn());
        $this->setOrderDir('ASC');

        return parent::beforeLoad();
    }

    /**
     * @return Collection
     * @throws \Exception
     * @since 2.0.0
     */
    protected function joinItemValueTable() : Collection
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

                // We change the filter alias based on the joins in the beforeLoad() fn
                $showSubNodeContentLevel = $this->getFilterItem()->getAttributes()->get('show_sub_node_contents_on_parent', 0);
                $filterTableAlias = $showSubNodeContentLevel ? 'tree2' : self::MAIN_TABLE_ALIAS;

                /*
                 * In the join the left side is the reference field's db column.
                 * The right side is the field's db column itself.
                 *
                 * A single field from the filters.xml gives the entire relationship and condition
                 */
                $this->join([
                    $ValueItemTableAlias => $valueItemRefConfiguration->getDbTable()
                ],
                    $filterTableAlias . '.' . $valueItemRefConfiguration->getValueId()->getReference()->getDbColumn() . '=' .
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
                // Use the language of the filter to show options (e.g. Categories) in the backend of JFilters
                if (Factory::getApplication()->isClient('administrator') && Factory::getApplication()->getInput()->getCmd('option',
                        '') == 'com_jfilters') {
                    $filterLanguage = $this->getFilterItem()->getLanguage();
                    $languages = ['*'];
                    if ($filterLanguage !== '*') {
                        $languages[] = $filterLanguage;
                    }
                } else {
                    $languages = [
                        Factory::getApplication()->getLanguage()->getTag(),
                        '*'
                    ];
                }

                $this->addCondition($contextTableAlias . '.' . $contextConfig->getItem()->getLanguage()->getDbColumn(),
                    $languages, 'IN');
            }

            $query->group(self::MAIN_TABLE_ALIAS . '.' . $this->database->quoteName($this->getFilterItem()->getConfig()->getValue()->getValue()->getDbColumn()));
        }

        return $this;
    }

    /**
     * Set the children and the parent Options for each Option.
     *
     * @return AbstractCollection
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function afterLoad(): AbstractCollection
    {
        parent::afterLoad();
        // do not recurse
        if (!$this->nestingProcessed) {
            // create the tree
            $this->createNested();
        }

        return $this;
    }
}
