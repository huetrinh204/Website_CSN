<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Site\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\ContextInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as ConfigFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as filterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\UriHandlerInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\MenuItemTrait;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule\Collection as sortingRuleCollection;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Finder\Administrator\Indexer\Query;
use Joomla\Component\Finder\Site\Model\SearchModel;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Filter\InputFilter;
use Joomla\Registry\Registry;

class ResultsModel extends SearchModel
{
    use MenuItemTrait;

    /**
     * The max number of results that can be returned in 1 query execution.
     * @since 1.0.0
     */
    protected const MAX_RESULTS_LIMIT = 4500;

    /**
     * Store the ids of the fetched link ids in an assoc. array (by context)
     *
     * @var array
     * @since 1.0.0
     */
    protected $ids;

    /**
     * @var bool
     * @since 1.0.0
     */
    protected bool $isSearch = false;

    /**
     * @var int
     * @since 1.0.0
     */
    protected $total;

    /**
     * @var filterCollection
     * @since 1.0.0
     */
    protected $filtersCollection;

    /**
     * @var ConfigFilterCollection
     * @since 1.8.0
     */
    protected $configFilterCollection;

    /**
     * @var ContextCollection
     * @since 1.0.0
     */
    protected $contextCollection;

    /**
     * @var ComponentConfig
     * @since 1.0.0
     */
    protected $componentConfig;

    /**
     * @var InputFilter
     * @since 1.0.0
     */
    protected $inputFilter;

    /**
     * @var bool
     * @since 1.0.0
     */
    protected bool $ignoreFiltersRequest = false;

    /**
     * @var bool
     * @since 1.0.0
     */
    protected $filterRequestVarFound;

    /**
     * Store the joined tables in the filters, to avoid duplicate joins
     *
     * @var array
     * @since 1.8.0
     */
    protected array $joinedTableAliases = [];

    /**
     * Stores the sorting rules as set in the menu item
     *
     * @var null|sortingRuleCollection
     * @since 1.16.0
     */
    protected $sortingRules;

    /**
     * @var \Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\UriHandler|Uri
     * @since 1.16.2
     */
    protected $uri;

    /**
     * SearchModel constructor.
     *
     * @param array $config
     * @param MVCFactoryInterface|null $factory
     *
     * @throws Exception
     * @since 1.0.0
     */
    public function __construct($config = array(), MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
        $objectManager = ObjectManager::getInstance();
        $this->filtersCollection = $objectManager->getObject(filterCollection::class);
        $this->filtersCollection->addCondition('filter.state', [1, 2]);
        $context = $this->getMenuItem() ? $this->getMenuItem()->getParams()->get('contextType', 'com_content.article') : 'com_content.article';
        $this->filtersCollection->addCondition('filter.context', $context);
        $this->configFilterCollection = $objectManager->getObject(ConfigFilterCollection::class);
        $this->contextCollection = $objectManager->getObject(ContextCollection::class);
        $this->componentConfig =  $objectManager->getObject(ComponentConfig::class);
        $this->sortingRules = $objectManager->getObject(sortingRuleCollection::class);
        $this->inputFilter  = new InputFilter();

        if (isset($config['ignore_filters_request']) && $config['ignore_filters_request']) {
            $this->ignoreFiltersRequest = true;
        }
    }

    /**
     * @param null $ordering
     * @param null $direction
     *
     * @throws Exception
     * @since 1.0.0
     */
    protected function populateState($ordering = null, $direction = null)
    {
        /** @var  CMSApplicationInterface $app */
        $app = Factory::getApplication();
        /** @var Registry $params */
        $params = $app->getParams();

        // Set the 'word_match' param as set in the com_finder. Otherwise, we may get no results (e.g. partial matching).
        $finderParams = $app->getParams('com_finder');
        $params->set('word_match', $finderParams->get('word_match', 'exact'));
        $params->set('allow_empty_query', $finderParams->get('allow_empty_query', '0'));

        parent::populateState();

        $input = $app->input;

        // We have a search: A. When a search term is used. B. When a SS filter is used without a search term and returns results.
        $request = $input->request;
        if ($request->getString('q') || ($finderParams->get('allow_empty_query') && !empty($request->get('t')))) {
            $this->isSearch = true;
        }

        $context = $this->getMenuItem() ? $this->getMenuItem()->getParams()->get('contextType', 'com_content.article') : 'com_content.article';
        $this->setState('context', $context);

        $this->setState('filter.language', Multilanguage::isEnabled());
    }

    /**
     * Return the item ids
     *
     * @return array
     * @throws Exception
     * @since 1.0.0
     */
    public function getItemIdsFromSearch()
    {
        $context = $this->getState('context', 'com_content.article');

        if (!isset($this->ids[$context])) {
            /* We only want the results from the search, without the filters */
            $this->ignoreFiltersRequest = true;
            /*
             * Check if the state is populated.
             * $this->searchquery is instantiated in populateState()
             * Avoid calling the populateState again as it includes db queries execution.
             */
            if ($this->searchquery === null) {
                $this->populateState();
            }
            if ($this->isSearch) {
                $query = $this->getListQuery();
                $query->clear('select')->clear('offset')->clear('order')->select('link_items.item_id');
                $query->setLimit(self::MAX_RESULTS_LIMIT, 0);
                $this->_db->setQuery($query);
                $this->ids[$context] = $this->_db->loadColumn();
                $this->total = count($this->ids);
            }
        }

        return $this->ids[$context] ?? [];
    }

    /**
     * @return QueryInterface
     * @throws Exception
     * @since 1.0.0
     */
    protected function getListQuery()
    {
        $contextParam = $this->getState('context', 'com_content.article');

        //if is search get the query from the parent class
        if ($this->isSearch) {
            $query = parent::getListQuery();
        } else {
            $query = $this->_db->getQuery(true);

            // Select the required fields from the table.
            $query->select(
                $this->getState(
                    'list.select',
                    'l.link_id, l.object'
                )
            )->from($this->_db->quoteName('#__finder_links', 'l'))
                  ->where('l.state = 1')
                  ->where('l.published = 1')
                // We use group to avoid multiple similar records in the results (See items using checkboxes fields).
                  ->group('l.link_id');

            //ACL
            $userGroups = array_unique(Factory::getApplication()->getIdentity()->getAuthorisedViewLevels());
            $query->whereIn($this->_db->quoteName('l.access'), $userGroups, ParameterType::INTEGER);

            // Get the current date, minus seconds.
            $nowDate = $this->_db->quote(substr_replace(Factory::getDate()->toSql(), '00', -2));

            // Add the publish up and publish down filters.
            $query->where('(l.publish_start_date IS NULL OR l.publish_start_date <= ' . $nowDate . ')')
                  ->where('(l.publish_end_date IS NULL OR l.publish_end_date >= ' . $nowDate . ')');

            // Language
            if (Multilanguage::isEnabled()) {
                $query->whereIn($this->_db->quoteName('l.language'),
                    [Factory::getApplication()->getLanguage()->getTag(), '*'], ParameterType::STRING);
            }
        }

        // `l` and `m` are generated in parent class
        $this->joinedTableAliases[] = 'l';
        $this->joinedTableAliases[] = 'm';

        $query->join('INNER',
            $this->_db->quoteName('#__jfilters_links_items') . ' AS link_items ON link_items.link_id=l.link_id');

        $this->joinedTableAliases[] = 'link_items';

        // Take into account the filters
        if (!$this->ignoreFiltersRequest) {
            $whereConditionsByFilter = [];
            $tableAliasConfigName = [];

            /** @var FilterInterface $filter */
            foreach ($this->filtersCollection as $filter) {

                //no var in the request
                if (empty($filter->getRequest())) {
                    continue;
                }
                $context = $filter->getContext();
                $dataType = $filter->getOptions()->getOptionDataType();
                $dbParamDataType = in_array($dataType, [
                    ParameterType::BOOLEAN => ParameterType::BOOLEAN,
                    ParameterType::INTEGER => ParameterType::INTEGER,
                    ParameterType::LARGE_OBJECT => ParameterType::LARGE_OBJECT,
                    ParameterType::NULL => ParameterType::NULL,
                    ParameterType::STRING => ParameterType::STRING,
                ]) ? $dataType : ParameterType::STRING;

                $valueConfig = $filter->getConfig()->getValue();
                $valueItemRefConfig = $filter->getConfig()->getValueItemRef();

                // We do not use joins for each of the filters under the same dynamic filter. Hence, they use the same table alias.
                $useTableNameAlias = $filter->getConfig()->isDynamic() && $filter->getConfig()->getValue()->getDbTable() === $filter->getConfig()->getValueItemRef()->getDbTable() && $filter->getConfig()->getValue()->getParentId();
                $valueItemTableAlias = $useTableNameAlias ? str_replace('#__', '', $filter->getConfig()->getValueItemRef()->getDbTable()) : 'valueItemRef' . $filter->getId();
                $whereCondition = '';

                if (!in_array($valueItemTableAlias, $this->joinedTableAliases)) {
                    $query->innerJoin($this->_db->quoteName($valueItemRefConfig->getDbTable()) . ' AS ' . $valueItemTableAlias . ' ON ' . $valueItemTableAlias . '.' . $this->_db->quoteName($valueItemRefConfig->getItemId()->getDbColumn()) . '= link_items.item_id');
                    $this->joinedTableAliases[] = $valueItemTableAlias;
                }
                // Check if we should use WHERE IN or WHERE LIKE or WHERE BETWEEN
                $operator = '';
                if (
                    ($dataType === 'date' &&
                        $filter->getIsRange()) ||
                    (($dataType === 'int' || $dataType === 'float') &&
                        $filter->getIsRange())
                ) {
                    $operator = 'BETWEEN';
                } elseif ($dataType === 'date' && !$filter->getAttributes()->get('show_time', 0)) {
                    $operator = 'LIKE';
                }
                if (empty($operator)) {
                    foreach ($filter->getRequest() as $requestValue) {
                        // If its is equal to the max length probably is cut, or if it's a date without time, use WHERE LIKE
                        if (!is_numeric($requestValue) && mb_strlen($requestValue) == $this->componentConfig->get('max_option_value_length', 35)) {
                            $operator = 'LIKE';
                        }
                    }
                }
                if (empty($operator)) {
                    $operator = 'IN';
                }

                if ($operator == 'IN') {

                    $whereCondition = $this->_db->quoteName(
                            $valueItemTableAlias . '.' . $valueItemRefConfig->getValueId()->getDbColumn()
                        ) . ' IN (' . implode(',', $query->bindArray($filter->getRequest(), $dbParamDataType)) . ')';

                    // Whether the parent node returns the sub-nodes results.
                    $subNodesOnParent = $filter->getConfig()->getValue()->getIsTree() && $filter->getAttributes()->get('show_sub_node_contents_on_parent',0);

                    // Emulate the selections of the sub-nodes, after a parent node selection
                    if ($subNodesOnParent && $filter->getConfig()->getValue()->getLft() && $filter->getConfig()->getValue()->getRgt()) {
                        $valuesTable = $filter->getConfig()->getValue()->getDbTable();
                        $lftDbColumn = $filter->getConfig()->getValue()->getLft()->getDbColumn();
                        $rgtDbColumn = $filter->getConfig()->getValue()->getRgt()->getDbColumn();
                        $statedColumn = $filter->getConfig()->getValue()->getState()->getDbColumn();

                        $subQuery = $this->_db->getQuery(true);
                        $subQuery->select('sub.id')
                                 ->from($this->_db->quoteName($valuesTable, 'sub'))
                                 ->join('INNER',
                                     $this->_db->quoteName($valuesTable, 'this'),
                                     $this->_db->quoteName('sub.'.$lftDbColumn) . '>' . $this->_db->quoteName('this.'.$lftDbColumn) . ' AND ' .
                                     $this->_db->quoteName('sub.'.$rgtDbColumn) . '<' . $this->_db->quoteName('this.'.$rgtDbColumn)
                                 )->where($this->_db->quoteName('this.' . $filter->getConfig()->getValue()->getValue()->getDbColumn()) . 'IN('. implode(',', $query->bindArray($filter->getRequest(), $dbParamDataType)) . ')');
                        if($statedColumn) {
                            $subQuery->where($this->_db->quoteName('sub.'.$statedColumn) . '=1');
                        }

                        $whereCondition = '(' .  $whereCondition . ' OR ' . $this->_db->quoteName(
                                $valueItemTableAlias . '.' . $valueItemRefConfig->getValueId()->getDbColumn()
                            ) . ' IN (' . (string)$subQuery . '))';
                    }

                }elseif($operator == 'LIKE'){
                    $dbColumn = $valueItemTableAlias . '.' . $valueItemRefConfig->getValueId()->getDbColumn();
                    $whereLikeValues = array_map(function ($value) use ($dbColumn, $dbParamDataType){
                        return $this->_db->quoteName($dbColumn) . ' LIKE ' . $this->_db->quote($this->inputFilter->clean($value, $dbParamDataType) .'%');
                    }, $filter->getRequest());

                    $whereCondition = '('.implode(' OR ', $whereLikeValues).')';
                }
                // Between
                else {
                    $dbColumn = $valueItemTableAlias . '.' . $valueItemRefConfig->getValueId()->getDbColumn();
                    $whereBetweenValues = $filter->getRequest();

                    if($dataType === 'date' && !$filter->getAttributes()->get('show_time', 0) && !empty($whereBetweenValues[1])) {
                        // We need to set the biggest possible time for the 2nd date
                        $whereBetweenValues[1] = $whereBetweenValues[1] . ' 23:59:59';
                    }

                    $whereConditionSubQueries = [];
                    if (isset($whereBetweenValues[0])) {
                        $fromValue = $this->inputFilter->clean($whereBetweenValues[0], $dbParamDataType);
                        // Do not quote numerical values. The query won't work as expected
                        $fromValue = $dataType === 'int' ? $fromValue : $this->_db->quote($fromValue);
                        $whereConditionSubQueries [] = $this->_db->quoteName($dbColumn) . ' >= ' . $fromValue;
                    }

                    if (isset($whereBetweenValues[1])) {
                        $toValue =  $this->inputFilter->clean($whereBetweenValues[1], $dbParamDataType);
                        $toValue = $dataType === 'int' ? $toValue : $this->_db->quote($toValue);
                        $whereConditionSubQueries [] = $this->_db->quoteName($dbColumn) . ' <= ' . $toValue;
                    }

                    if ($whereConditionSubQueries) {
                        $whereCondition = '(' . implode(' AND ', $whereConditionSubQueries) . ')';
                    }
                }

                // Add a reference to the parent field (e.g. field id for the fields). But only if the Value and the valueItemRef tables are the same.
                if ($valueConfig->getDbTable() == $valueItemRefConfig->getDbTable() && $valueConfig->getParentId()) {
                    $whereCondition .= ' AND ' . $this->_db->quoteName($valueItemTableAlias . '.' . $valueConfig->getParentId()->getDbColumn()) . '=' . $filter->getParentId();
                    $whereCondition = '(' . $whereCondition . ')';
                }

                if (!isset($whereConditionsByFilter[$valueItemTableAlias])) {
                    $whereConditionsByFilter[$valueItemTableAlias] = [];
                    $tableAliasConfigName[$valueItemTableAlias] = $filter->getConfig()->getName();
                }
                $whereConditionsByFilter[$valueItemTableAlias][] = $whereCondition;

                // There is a reference to the type of the returned item
                if ($valueItemRefConfig->getTypeId() && $valueItemRefConfig->getTypeId()->getDbColumn()) {

                    /** @var ContextInterface $contextConfiguration */
                    $contextConfiguration = $this->contextCollection->getByNameAttribute($context);
                    if ($contextConfiguration && $contextConfiguration->getTypeId()) {
                        $typeId = $contextConfiguration->getTypeId();
                        $query->where($this->_db->quoteName($valueItemTableAlias . '.' . $valueItemRefConfig->getTypeId()->getDbColumn()) . '= :typeId' . $filter->getId())
                              ->bind(':typeId' . $filter->getId(), $typeId, ParameterType::INTEGER);
                    }
                }
            }

            // Add the `WHERE` by filter type to the query
            foreach ($whereConditionsByFilter as $tableAlias => $whereConditions) {
                $wherePerFilterType = '('.implode(' OR ', $whereConditions).')';
                $query->where($wherePerFilterType);
                $fieldConfigName = $tableAliasConfigName[$tableAlias];
                $filterConfig = $this->configFilterCollection->getByNameAttribute($fieldConfigName);
                if ($filterConfig->isDynamic() && $filterConfig->getValue()->getDbTable() === $filterConfig->getValueItemRef()->getDbTable() && $filterConfig->getValue()->getParentId() && count($whereConditions) > 1) {
                    $parentIdColumn = $filterConfig->getValue()->getParentId()->getDbColumn();
                    $query->having('COUNT(DISTINCT(' . $this->_db->quoteName($tableAlias . '.' . $parentIdColumn) . ')) = ' . count($whereConditions));
                }
            }
        }

        /**
         * This is a B/C code.
         * Maybe the 'contextType' setting is not set in the menu item (e.g. update from a previous version).
         * In that case we get the default 'com_content.article', but we do not know if it's the real one.
         * @todo remove those lines in upcoming releases.
         */
        if (!empty($context) && $contextParam != $context) {
            $contextParam = $context;
        }
        $query->where($this->_db->quoteName('link_items.context') . '= :context')->bind(':context', $contextParam);

        // Exclude item
        if ($this->getState('excludedItemId')) {
            $excludedIds = [$this->getState('excludedItemId')];
            $query->whereNotIn($this->_db->quoteName($valueItemTableAlias . '.' . $valueConfig->getValue()->getDbColumn()), $excludedIds);
        }


        /* -- Sorting -- */

        /** @var SortingRule $sortingRule */
        $sortingRule = $this->sortingRules->getCurrent();

        if ($sortingRule) {
            $sortingRuleDbTableName = $sortingRule->getSortField()->getDbTableName();
            $sortFieldTableAlias = $sortingRuleDbTableName;
            $sortingFieldName = $sortingRule->getSortField()->getFieldName();

            // Sorting with filters
            if ($sortingRule->getSortField()->getIsFilter()) {
                /** @var FilterInterface $orderingFilter */
                $orderingFilter = $this->filtersCollection->getByAttribute('id', (int)$sortingRule->getSortField()->getFieldName());

                if ($orderingFilter) {
                    $sortingRuleDbTableName = $orderingFilter->getConfig()->getValue()->getDbTable();
                    $sortingFieldName = $orderingFilter->getConfig()->getValue()->getLabel()->getDbColumn();

                    $itemValueXrefTable = $orderingFilter->getConfig()->getValueItemRef()->getDbTable();
                    $itemIdColumnXref = $orderingFilter->getConfig()->getValueItemRef()->getItemId()->getDbColumn();
                    $sortFieldTableAlias = 'sortingTable';
                    /** @var QueryInterface $subQuerySorting */
                    $subQuerySorting = $this->_db->getQuery(true);
                    $subQuerySorting->select($this->_db->quoteName($sortingFieldName, 'sortingField'))->
                    select($this->_db->quoteName($orderingFilter->getConfig()->getValue()->getValue()->getDbColumn(), 'sortFieldId'))->
                    from($this->_db->quoteName($sortingRuleDbTableName));

                    if ($orderingFilter->getConfig()->getValue()->getParentId()) {
                        $subQuerySorting->where($this->_db->quoteName($orderingFilter->getConfig()->getValue()->getParentId()->getDbColumn()) . '= :sortingFilterId');
                        $parentId = $orderingFilter->getParentId();
                        $query->bind(':sortingFilterId', $parentId, ParameterType::INTEGER);
                    }

                    if ($sortingRuleDbTableName == $itemValueXrefTable) {
                        $subQuerySorting->select($this->_db->quoteName($itemIdColumnXref, 'item_id'));
                        $query->leftjoin('(' . $subQuerySorting . ') AS ' . $sortFieldTableAlias . ' ON ' . $sortFieldTableAlias . '.item_id = link_items.item_id');

                    }
                    // Join the values table if it's different from the xref table
                    else {
                        $itemValueXrefTableAlias = $this->getJoinedTableAlias($query, $itemValueXrefTable);
                        $itemValueXrefTableAlias = !empty($itemValueXrefTableAlias) ? $itemValueXrefTableAlias : 'itemRefAlias';
                        // Join the xref table
                        $query->leftjoin($this->_db->quoteName($itemValueXrefTable) . ' AS ' . $itemValueXrefTableAlias . ' ON ' . $itemValueXrefTableAlias . '.' . $this->_db->quoteName($itemIdColumnXref) . '= link_items.item_id');
                        $query->leftjoin('(' . $subQuerySorting . ') AS ' . $sortFieldTableAlias . ' ON ' . $sortFieldTableAlias . '.sortFieldId =' . $itemValueXrefTableAlias . $this->_db->quoteName($itemIdColumnXref));
                    }

                    $sortingFieldName = 'sortingField';
                }
            }

            // Join the context table
            elseif (!in_array($sortingRuleDbTableName, $this->joinedTableAliases)) {
                // Iterate through the joins anc check if exists
                $sortFieldTableAlias = $this->getJoinedTableAlias($query, $sortingRuleDbTableName);
                if (!$sortFieldTableAlias) {
                    $sortFieldTableAlias = 'sortingTable';
                    $this->joinedTableAliases[] = $sortFieldTableAlias;
                    $contextConfiguration = $contextConfiguration ?? $this->contextCollection->getByNameAttribute($contextParam);
                    $query->rightJoin($this->_db->quoteName($sortingRuleDbTableName) . ' AS ' . $sortFieldTableAlias . ' ON ' . $sortFieldTableAlias . '.' . $this->_db->quoteName($contextConfiguration->getItem()->getId()->getDbColumn()) . '= link_items.item_id');
                }
            }

            if ($sortFieldTableAlias && $sortingFieldName) {
                $ordering = $sortFieldTableAlias . '.' . $sortingFieldName;

                // Include the sort field in the select for clarity reasons.
                /*
                 * 'relevance' and 'm.weight' (B/C) should be an aggregate field.
                 */
                if ($sortingFieldName === 'relevance' || $sortingFieldName === 'weight') {
                    $query->select('SUM(' . $this->_db->escape('m.weight') . ') AS relevance');
                    $ordering =  'relevance';
                }else {
                    $query->select($this->_db->quoteName($ordering));
                    // ONLY_FULL_GROUP_BY mode requires that all the columns are used either in the GROUP By clause, or in aggregate functions.
                    $query->group($this->_db->quoteName($ordering));
                }

                // Set order
                // Treat numerical filters as numbers when ordering
                if (isset($orderingFilter) && $orderingFilter->getAttributes()->get('dataType') == 'float') {
                    $orderClause = 'CAST(' . $this->_db->quoteName($ordering) .' AS DECIMAL(10,2))';
                }elseif (isset($orderingFilter) && $orderingFilter->getAttributes()->get('dataType') == 'int') {
                    $orderClause = 'CAST(' . $this->_db->quoteName($ordering) .' AS SIGNED)';
                }
                else {
                    $orderClause = $this->_db->quoteName($ordering);
                }
                $query->clear('order')->order($orderClause . ' ' . $this->_db->escape($sortingRule->getSortDirection()));

                // Add a secondary ordering field.
                if (!$this->isSearch && $ordering != SortingRule::DEFAULT_SORTING_FILTERING_FIELD) {
                    $query->order($this->_db->quoteName(SortingRule::DEFAULT_SORTING_FILTERING_FIELD) . ' ' . $this->_db->escape(SortingRule::DEFAULT_SORTING_FILTERING_DIR));
                }
            }
        }
        return $query;
    }

    /**
     * Trace the table alias from the query's join clauses
     * Can be used to also check if a join exists
     *
     * @param QueryInterface $query
     * @param string $tableName
     * @return string
     * @since 1.16.0
     */
    protected function getJoinedTableAlias(QueryInterface $query, string $tableName) : string
    {
        $tableAlias = '';
        foreach ($query->join as $join) {
            foreach ($join->getElements() as $element) {
                if (preg_match('/' . $tableName . '`?\s+/', $element,)) {
                    $joinParts = explode(' AS ', $element);
                    if ($joinParts) {
                        $asClause = explode(' ', $joinParts[1]);
                        $tableAlias = reset($asClause);
                    }
                    break (2);
                }
            }
        }

        return $tableAlias;
    }

    /**
     * @return array
     * @throws Exception
     * @since 1.0.0
     */
    public function getItems()
    {
        // No search and no filtering
        if (!$this->isSearch && !$this->getFilterRequestVarFound()) {
            return null;
        }
        $items = parent::getItems();
        $items = $items === null ? [] : $items;

        // Load custom fields in YOOtheme Pro that can be used in the results.
        if (PluginHelper::isEnabled('system', 'jfiltersyootheme')) {
            PluginHelper::importPlugin('content');
            // Store the tagged items
            $taggedItems = [];
            foreach ($items as $item) {
                /*
                 * We cannot directly assign `jcfields` to the result item, due to inherent issues in the code of the plugins/system/fields/fields.php
                 * Hence we create a temporary object for that.
                 */
                $itemTmp = new \stdClass();
                $itemTmp->id = $item->id;
                // Contact result items, missing `context` attribute.
                $itemTmp->context = $item->context ?? (isset($item->extension) && isset($item->layout) ? $item->extension . '.' . $item->layout : $this->getState('context', 'com_contact.contact'));
                $itemTmp->language = $item->language;
                $itemTmp->catid = $item->catid;
                $itemTmp->text = $item->text ?? '';
                Factory::getApplication()->triggerEvent('onContentPrepare', [$itemTmp->context, $itemTmp, $item->params, 0]);

                if(!empty($itemTmp->jcfields)) {
                    PluginHelper::importPlugin('fields');
                    foreach ($itemTmp->jcfields as $field) {
                        Factory::getApplication()->triggerEvent('onCustomFieldsBeforePrepareField', [
                            $itemTmp->context,
                            $itemTmp,
                            &$field,
                        ]);
                    }
                }

                $fields = [];
                if (!empty($itemTmp->jcfields)) {
                    foreach ($itemTmp->jcfields as $field) {
                        $fields[$field->name] = $field;
                    }
                }
                $item->_fields = $fields;

                $item->tags             = new TagsHelper();
                $taggedItems[$item->id] = $item;
            }

            // Load tags of all items.
            if ($taggedItems) {
                $tagsHelper = new TagsHelper();
                $itemIds    = array_keys($taggedItems);

                foreach ($tagsHelper->getMultipleItemTags(reset($items)->context, $itemIds) as $id => $tags) {
                    $taggedItems[$id]->tags->itemTags = $tags;
                }
            }
        }
        return $items;
    }

    /**
     * @return bool
     * @throws Exception
     * @since 1.0.0
     */
    protected function getFilterRequestVarFound()
    {
        if ($this->filterRequestVarFound === null) {
            $this->filterRequestVarFound = false;
            if (!$this->ignoreFiltersRequest) {
                /** @var FilterInterface $filter */
                foreach ($this->filtersCollection as $filter) {

                    //no var in the request
                    if (empty($filter->getRequest())) {
                        continue;
                    }
                    $this->filterRequestVarFound = true;
                    break;
                }
            }
        }
        return $this->filterRequestVarFound;
    }

    /**
     * Get the total number of items found for the pagination
     *
     * @return false|int
     * @throws Exception
     * @since 1.0.0
     */
    public function getTotal()
    {
        // No search and no filtering
        if (!$this->isSearch && !$this->getFilterRequestVarFound()) {
            $this->total = 0;
        } elseif ($this->total === null || $this->getFilterRequestVarFound()) {
            $this->total = parent::getTotal();
        }
        return $this->total;
    }

    /**
     * Override the function to simplify the query, even more
     *
     * @param   DatabaseQuery|string  $query
     *
     * @return int
     * @since 1.0.0
     */
    protected function _getListCount($query)
    {
        // Remove the limit and offset part if it's a DatabaseQuery object
        if ($query instanceof DatabaseQuery)
        {
            $query = clone $query;
            $query->clear('limit')->clear('offset')->clear('order')->clear('select')->select('l.link_id');
        }

        $this->getDbo()->setQuery($query);
        $this->getDbo()->execute();

        return (int) $this->getDbo()->getNumRows();
    }

    /**
     * Override the function to avoid executing the query when nothing is returned
     *
     * @return Query
     * @throws Exception
     * @since 1.0.0
     */
    public function getQuery()
    {
        if ($this->searchquery === null) {
            $this->searchquery = new Query([]);
        }

        //without that it does not load the results layout
        $this->searchquery->search = true;

        // Return the query object.
        return $this->searchquery;
    }


    /**
     * Get the results page's uri
     *
     * @return Uri
     * @throws \ReflectionException
     * @since 1.16.0
     */
    protected function getUri() : Uri
    {
        if ($this->uri === null) {
            $objectManager = ObjectManager::getInstance();
            /** @var UriHandlerInterface $uriHandler */
            $uriHandler = $objectManager->getObject(UriHandlerInterface::class);

            /** @var FilterInterface $dummyFilter */
            $dummyFilter = $objectManager->createObject(FilterInterface::class);
            $dummyFilter->setId(0);
            $dummyFilter->setRoot(false);
            /** @var  OptionInterface $dummyOption */
            $dummyOption = $objectManager->createObject(OptionInterface::class);
            $dummyOption->setParentFilter($dummyFilter);
            $this->uri = $uriHandler->getBase($dummyOption);
            $itemId = $this->getMenuItem() ? $this->getMenuItem()->id : 0;
            $this->uri->setVar('Itemid', $itemId);
        }

        return $this->uri;
    }

    /**
     * Returns an array of sorting fields, ready to be used by the view.
     * This function is called by the view.
     *
     * @return array
     * @throws Exception
     * @since 1.16.0
     */
    public function getSortFields() : array
    {
        $sortFields = [];
        /** @var SortingRule $sortingRule */
        foreach ($this->sortingRules as $sortingRule) {
            $label = $sortingRule->getLabel();
            // If no label found, the sorting rule is not valid (e.g, a set filter is not loaded)
            if ($label) {
                $url = $this->getUri();
                $url->setVar('o', $sortingRule->getSortField()->getFieldName());
                $url->setVar('od', strtolower($sortingRule->getSortDirection()));
                if (SortingRule::DEFAULT_SORTING_FILTERING_DIR == $sortingRule->getSortDirection()) {
                    $url->delVar('od');
                }
                $sortingObj = new \stdClass();
                $sortingObj->label = $label;
                $sortingObj->url = $url->toString();
                $sortingObj->active = $sortingRule == $this->sortingRules->getCurrent();
                $sortFields[] = $sortingObj;
            }
        }

        return $sortFields;

    }
}
