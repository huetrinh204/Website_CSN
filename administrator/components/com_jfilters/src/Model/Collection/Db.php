<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2010-2019 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\Collection;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\AbstractCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;

class Db extends AbstractCollection
{
    /**
     * The alias used for the main table of the collection
     */
    public const MAIN_TABLE_ALIAS = 'main';

    /**
     * @since 1.0.0
     * @var DatabaseInterface
     */
    protected $database;

    /**
     * @since 1.0.0
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @since 1.0.0
     * @var
     */
    protected $query;

    /**
     * @since 1.0.0
     * @var
     */
    protected $mainTable;

    /**
     * @since 1.0.0
     * @var array
     */
    protected $joinedTables = [];

    /**
     * Holds the ['alias'=>'tableName']
     *
     * @since 1.0.0
     * @var array
     */
    protected $tableNames = [];

    /**
     * Stores the items as raw objects (/stdclass)
     *
     * @since 1.0.0
     * @var array
     */
    protected $itemsRaw = [];

    /**
     * Collection constructor.
     * @param DatabaseInterface $database
     */
    public function __construct(
        DatabaseInterface $database,
        LoggerInterface $logger)
    {
        $this->database = $database;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * Called at the start of the collection load
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    protected function init(): AbstractCollection
    {
        $this->getQuery()->clear('from');
        $this->getQuery()->from($this->getMainTable() . ' AS ' . self::MAIN_TABLE_ALIAS);
        return parent::init();
    }

    /**
     * Escape a value, to be used in a query
     *
     * @param string|array $value
     *
     * @return array|string
     * @since 1.0.0
     */
    protected function escapeValue($value)
    {
        if(is_array($value)) {
            $newValue = array_map(function($val) {
                return is_int($val) || is_float($val) ? $this->database->escape($val) : $this->database->quote($val);
            }, $value);
        }
        else {
            $newValue = is_int($value) || is_float($value) ? $this->database->escape($value) : $this->database->quote($value);
        }

        return $newValue;
    }

    /**
     * Escape a name, to be used in a query
     *
     * @param   string  $fieldName
     *
     * @return string
     * @since 1.0.0
     */
    protected function escapeName(string $fieldName): string
    {
        return $this->database->quoteName($fieldName);
    }

    /**
     * Join table to collection select
     *
     * @param string|array $table , array used for [alias=>table]
     * @param string $cond
     * @param string $cols
     * @return Db
     * @since 1.0.0
     */
    public function join($table, string $cond, $cols = ''): Db
    {
        $alias = '';

        /*
         * the $table could be an array of  [$alias=>'tableName']
         */
        if (is_array($table)) {
            foreach ($table as $alias => $v) {
                $table = $v;
                $this->setTableAlias($table, $alias);
                break;
            }
        }

        $table = $this->getTableNameByAlias($alias);
        if (!isset($this->joinedTables[$alias])) {
            $cond = $this->resolveSQLCondition($cond);
            if (strpos($table, '`') === false) {
                $table = $this->escapeName($table);
            }
            $this->getQuery()->join('LEFT', $table . ' AS  ' . $alias, $cond);
            $this->joinedTables[$alias] = true;
        }
        if (!empty($cols)) {
            $this->addColumnToSelect($cols);
        }
        return $this;
    }

    /**
     * Resolve tha table alias
     *
     * @param $alias
     * @return string
     * @since 1.0.0
     */
    protected function getTableNameByAlias($alias): string
    {
        $tableName = $alias;
        if (isset($this->tableNames[$alias])) {
            $tableName = $this->tableNames[$alias];
        }
        return $tableName;
    }

    /**
     * Replaces the table names with their respective aliases in the sql conditions
     *
     * @param string $condition
     * @return string
     * @since 1.0.0
     */
    protected function resolveSQLCondition(string $condition): string
    {
        // Find the table, in the condition
        preg_match_all('/((#__)?[a-z0-9_-]+\.)/i', $condition, $matches);
        foreach ($matches[0] as $alias) {
            //remove dots from the tableName
            $alias = trim($alias, '.');
            $tableName = $this->getTableNameByAlias($alias);
            $alias = $this->escapeName($alias);
            $condition = str_replace($tableName, $alias, $condition);
        }
        return $condition;
    }

    /**
     * @param $column
     * @param null $alias
     * @param bool $distinct
     * @return Db
     * @since 1.0.0
     */
    public function addColumnToSelect($column, $alias = null, $distinct = false): Db
    {
        if ($column === '*') {
            // If we will select all fields
            $this->getQuery()->select('*');
            return $this;
        }

        if (is_array($column)) {
            foreach ($column as $key => $value) {
                $this->addColumnToSelect($value, is_string($key) ? $key : null);
            }

            $this->clearItems();
            return $this;
        }

        // We may use another statement in parenthesis (e.g. (SELECT COUNT(*))), instead of a single column.
        $column = strpos($column, '(') === false ? $this->escapeName($column) : $column;
        if ($alias === null) {
            $this->getQuery()->select($column);
        } else {
            $column = $distinct ? 'DISTINCT (' . $column . ')' : $column;
            $this->getQuery()->select($column . ' AS ' . $this->escapeName($alias));
        }

        $this->clearItems();
        return $this;
    }

    /**
     * Clear the items as well as the query and conditions
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    public function clear(): AbstractCollection
    {
        $this->query = null;
        $this->joinedTables = [];
        return parent::clear();
    }

    /**
     * Called before the collection loading
     * Can be used for preconditions or pre-calls
     *
     * @return Db
     * @since 1.0.0
     */
    protected function beforeLoad(): Db
    {
        return $this;
    }

    /**
     * Called before executing the query.
     * Useful for rendering fields and resolving the query
     *
     * @return Db
     * @since 1.0.0
     */
    protected function beforeExecution(): Db
    {
        $this->renderConditions();
        $this->renderOrder();
        return $this;
    }

    /**
     * Renders the ordering and assigns it to the query.
     *
     * @return $this
     * @since 1.0.0
     */
    protected function renderOrder()
    {
        if (empty($this->orderField)) {
            return $this;
        }

        if (is_array($this->getOrderField())) {
            $sortField = array_map([$this, 'escapeName'], $this->getOrderField());
        } else {
            $sortField = $this->escapeName($this->getOrderField());
        }

        // We have a predefined set of of values that designates the ordering
        if (!empty($this->orderFieldValues)) {
            $orderFieldValues = $this->escapeValue($this->orderFieldValues);
            $field = $sortField;
            $restFields = '';

            if (is_array($sortField)) {
                $field = reset($sortField);
                // Add the rest fields which should be appended at the end of the order by clause.
                $restFields = ',' . implode(',', $sortField);
            }

            $orderBy = 'FIELD(' . $field . ',' . implode(',', $orderFieldValues) . ')' . ' ' . $this->orderDir . ' ' . $restFields;
        } elseif (is_array($sortField)) {
            $orderBy = reset($sortField) . ' ' . $this->orderDir;
            $orderBy .= !empty($sortField) ? ',' . implode(',', $sortField) : '';
        } else {
            $orderBy = $sortField . ' ' . $this->orderDir;
        }

        $this->getQuery()->order($orderBy);

        return $this;
    }

    /**
     * Renders the conditions to have meaning to the collection's filtering
     *
     * @return Db
     * @since 1.0.0
     */
    protected function renderConditions(): Db
    {
        // Iterate the conditionGroups and render their conditions as a single condition with ()
        foreach ($this->conditionGroups as $conditionGroup) {
            $conditionAndSql = [];
            $conditionOrSql = [];

            foreach ($conditionGroup->getConditions('AND') as $conditionAnd) {
                if (!empty($conditionAnd)) {
                    $conditionAndSql [] = $conditionAnd;
                }
            }

            foreach ($conditionGroup->getConditions('OR') as $conditionOr) {
                if (!empty($conditionOr)) {
                    $conditionOrSql [] = $conditionOr;
                }
            }

            $conditionAndSqlString = !empty($conditionAndSql) ? '(' . implode(' AND ', $conditionAndSql) . ')' : '';
            $conditionOrSqlString = !empty($conditionOrSql) ? '(' . implode(' OR ', $conditionOrSql) . ')' : '';

            if ($conditionAndSqlString) {
                if ($conditionGroup->getGlue() == 'OR') {
                    $this->getQuery()->orWhere($conditionAndSqlString);
                } else {
                    $this->getQuery()->where($conditionAndSqlString);
                }
            }

            if ($conditionOrSqlString) {
                if ($conditionGroup->getGlue() == 'OR') {
                    $this->getQuery()->orWhere($conditionOrSqlString);
                } else {
                    $this->getQuery()->where($conditionOrSqlString);
                }
            }
        }

        return $this;
    }

    /**
     * Returns the query
     *
     * @return QueryInterface
     * @since 1.0.0
     */
    public function getQuery(): QueryInterface
    {
        if ($this->query === null) {
            $this->query = $this->database->getQuery(true);
        }
        return $this->query;
    }

    /**
     * Get the main table's name
     *
     * @return string
     * @since 1.0.0
     */
    public function getMainTable(): string
    {
        if ($this->mainTable === null) {
            throw new \RuntimeException('Table is not defined for the collection:' . Db::class);
        }

        return $this->mainTable;
    }

    /**
     * Set the collection's main table
     *
     * @param $mainTable
     * @return Db
     * @since 1.0.0
     */
    public function setMainTable($mainTable): Db
    {
        $this->mainTable = $mainTable;
        $this->setTableAlias($this->mainTable, 'main');
        return $this;
    }

    /**
     * Set a table alias
     *
     * @param string $tableName
     * @param string $alias
     * @return Db
     * @since 1.0.0
     */
    protected function setTableAlias(string $tableName, string $alias): Db
    {
        if (!isset($this->tableNames[$alias])) {
            if (!empty($tableName) && !empty($alias)) {
                $this->tableNames[$alias] = $tableName;
            } else {
                throw new \RuntimeException('The $tableName or the $alias are empty');
            }
        }
        return $this;
    }

    /**
     * Contains the real logic for getting the collection's items
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    public function loadWithFilters(): AbstractCollection
    {
        $this->beforeLoad();
        $this->beforeExecution();
        $q = \JDEBUG || \defined('JFTESTDEBUG') ? (string)$this->getQuery() : '';
        try {
            $this->database->setQuery($this->getQuery());
            $this->itemsRaw = $this->database->loadAssocList();
        } catch (\RuntimeException $exception) {
            $this->logger->critical($exception);
            throw $exception;
        }
        $this->setMappedItems($this->itemsRaw);
        return parent::loadWithFilters();
    }
}
