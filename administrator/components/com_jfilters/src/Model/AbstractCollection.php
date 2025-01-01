<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Collection\ConditionGroup;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Profiler\Profiler;
use Joomla\Database\QueryInterface;

abstract class AbstractCollection implements \IteratorAggregate
{
    use ObjectMapperTrait;

    /**
     * @var bool
     * @since 1.0.0
     */
    protected $isInitialized = false;

    /**
     * @var array
     * @since 1.0.0
     */
    protected $items;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $itemObjectClass;

    /**
     * @var ConditionGroup[]
     */
    protected $conditionGroups = [];

    /**
     * @var bool
     * @since 1.0.0
     */
    protected $isLoaded = false;

    /**
     * @var ObjectManager
     * @since 1.0.0
     */
    protected $objectManager;

    /**
     * @var int
     * @since 1.0.0
     */
    protected $totalRecords;

    /**
     * @var array|string
     * @since 1.0.0
     */
    protected $orderField;

    /**
     * @var array
     * @since 1.3.0
     */
    protected $orderFieldValues;

    /**
     * @var array|string
     * @since 1.0.0
     */
    protected $orderDir = 'ASC';

    /**
     * @var ListModel
     * @since 1.0.0
     */
    protected $resourceModel;

    /**
     * AbstractCollection constructor.
     */
    public function __construct()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * Perform basic tasks on class initialization
     *
     * @return $this
     * @since 1.0.0
     */
    protected function init(): AbstractCollection
    {
        $this->isInitialized = true;

        return $this;
    }

    /**
     * @return \ArrayIterator|\Traversable
     * @since 1.0.0
     */
    public function getIterator(): \Traversable
    {
        $this->load();

        return new \ArrayIterator($this->getItems());
    }

    /**
     * Returns an empty item of the type defined in the collection's itemObjectClass property
     *
     * @return object
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function getEmptyItem()
    {
        return $this->objectManager->createObject($this->itemObjectClass);
    }

    /**
     * load the collection
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    public function load(): AbstractCollection
    {
        if ($this->isLoaded) {
            return $this;
        }
        \defined('JF_PROFILING') && \JF_PROFILING ? Profiler::getInstance('Application')->mark('beforeJFiltersCollection-' . get_class($this)) : null;
        $this->init();
        $this->clearItems();
        $this->loadWithFilters();
        $this->afterLoad();
        $this->sort();
        \defined('JF_PROFILING') && \JF_PROFILING ? Profiler::getInstance('Application')->mark('afterJFiltersCollection-' . get_class($this)) : null;

        return $this;
    }

    /**
     * Add actions after the collection loading
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    protected function afterLoad(): AbstractCollection
    {
        //add actions to sub-classes
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
        //add the logic in the sub-classes
        $this->isLoaded = true;

        return $this;
    }

    /**
     * Set logic in child classes
     *
     * @param   false  $force
     *
     * @return $this
     * @since 1.0.0
     */
    public function sort($force = false) : AbstractCollection
    {
        return $this;
    }

    /**
     * Add an item to the collection
     *
     * @param             $item
     * @param   int|null  $position
     *
     * @return $this
     * @since 1.0.0
     */
    public function addItem($item, ?int $position = null)
    {
        if (!$item instanceof $this->itemObjectClass) {
            throw new \InvalidArgumentException('The supplied item is not of the type, that the collection uses.');
        }

        if ($position !== null && $this->items != null) {
            array_splice($this->items, $position, 0, [$item]);
        } else {
            $this->items [] = $item;
        }
        $this->isLoaded = true;

        return $this;
    }

    /**
     * Remove an item from the collection
     *
     * @param           $item
     * @param   string  $key
     *
     * @return $this
     * @since 1.0.0
     */
    public function remove($item, $key = '')
    {
        $items = $this->getItems();

        if (empty($key)) {
            foreach ($items as $index => $itemsItem) {
                if ($itemsItem === $item) {
                    $key = $index;
                    break;
                }
            }
        }
        if (isset($this->items[$key])) {
            unset($this->items[$key]);
        }

        return $this;
    }

    /**
     * @return array
     * @since 1.0.0
     */
    public function getItems(): array
    {
        $this->load();

        return $this->items;
    }

    /**
     * Return an item or array of items, by a property and it's value.
     *
     * @param   string  $attribute
     * @param   string  $value
     * @param   bool    $returnSingle  Indicates if a single item or an array of items will be returned
     *
     * @return object|null|array
     * @since 1.0.0
     */
    public function getByAttribute(string $attribute, string $value, $returnSingle = true)
    {
        $this->load();
        $foundObjects = [];
        // Not let the case fool it
        $value = mb_strtolower($value);
        foreach ($this->items as $object) {
            $foundObject = null;
            $objectVars  = get_object_vars($object);
            if (in_array($attribute, $objectVars)) {
                try {
                    if (mb_strtolower($object->{$attribute}) == $value) {
                        $foundObject = $object;
                    }
                } catch (\Exception $exception) {
                    //suck it. The property exists but not accessible. Go on.
                }
            }
            // Maybe the property exists but is not accessible.
            if (!$foundObject) {
                $parts     = explode('_', $attribute);
                $attribute = '';
                foreach ($parts as $part) {
                    $attribute .= ucfirst($part);
                }
                $funcionName = 'get' . $attribute;
                $methodExists = method_exists($object, $funcionName);
                if ($methodExists) {
                    $functionReturnValue = $object->{$funcionName}();
                    $foundObject = mb_strtolower($functionReturnValue??'') == $value ? $object : false;
                }
            }
            if ($foundObject) {
                if ($returnSingle) {
                    return $foundObject;
                }
                $foundObjects[] = $foundObject;
            }
        }

        return $returnSingle ? null : $foundObjects;
    }

    /**
     * Get the position of an element in the collection by an attribute.
     *
     * @param   string  $attribute
     * @param   string  $value
     *
     * @return int|null
     * @since 1.0.0
     */
    public function getPosition(string $attribute, string $value): ?int
    {
        $this->load();
        $position = null;
        foreach ($this->items as $key => $object) {
            $objectVars = get_object_vars($object);
            if (in_array($attribute, $objectVars)) {
                try {
                    if ($object->{$attribute} == $value) {
                        $position = $key;
                        break;
                    }
                } catch (\Exception $exception) {
                    //suck it. The property exists but not accessible. Go on.
                }
            }
            // maybe the property exists but is not accessible.
            if ($position === null) {
                $parts = explode('_', $attribute);
                $attribute = '';
                foreach ($parts as $part) {
                    $attribute .= ucfirst($part);
                }
                $funcionName = 'get' . $attribute;
                $exist = method_exists($object, $funcionName);
                if ($exist && $object->{$funcionName}() == $value) {
                    $position = $key;
                    break;
                }
            }
        }

        return $position;
    }

    /**
     * Retrieve collection all items count
     *
     * @return int
     * @since 1.0.0
     */
    public function getSize(): int
    {
        $this->load();
        $this->totalRecords = count($this->getItems());

        return intval($this->totalRecords);
    }

    /**
     * Clear the collection's items
     *
     * @param   bool  $reload
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    public function clearItems($reload = true): AbstractCollection
    {
        if ($reload) {
            $this->isLoaded = false;
        }
        $this->items = [];

        return $this;
    }

    /**
     * Clear the collection with the conditions
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    public function clear(): AbstractCollection
    {
        $this->clearItems();
        $this->conditionGroups= [];

        return $this;
    }

    /**
     * Set the collection's order field
     *
     * @param   string|array  $orderField
     * @param   array  $fieldValues This allows us to created "ORDER BY FIELD($orderField, $fieldValue1, $fieldValue2)";
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    public function setOrderField($orderField = 'ordering', $fieldValues = []): AbstractCollection
    {
        $this->orderField = $orderField;
        $this->orderFieldValues = $fieldValues;
        if (isset($this->resourceModel) && $this->resourceModel instanceof BaseModel) {
            $this->resourceModel->setState('list.ordering', $orderField);
        }

        return $this;
    }

    /**
     * Get the collection's order field
     *
     * @return array|string
     * @since 1.0.0
     */
    public function getOrderField()
    {
        return $this->orderField;
    }

    /**
     * Set the collection's order direction field
     *
     * @param   string  $orderDir
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    public function setOrderDir(string $orderDir = 'ASC'): AbstractCollection
    {
        $this->orderDir = $orderDir;
        if (isset($this->resourceModel) && $this->resourceModel instanceof BaseModel) {
            $this->resourceModel->setState('list.direction', $orderDir);
        }

        return $this;
    }

    /**
     * Add condition to the collection.
     * Setting a new condition will clear the existing collection.
     * Groups allows us to use a set of conditions as a single one.
     * e.g. in sql WHERE ((group1Condition1 OR group1Condition2) AND (group2Condition1 OR group2Condition2))
     *
     * @param   string        $field
     * @param   string|array  $value
     * @param   string        $operator
     * @param   string        $glue
     * @param   string        $groupId The group id to which the condition belongs.
     *
     * @return AbstractCollection
     * @since 1.0.0
     */
    public function addCondition(
        string $field,
        $value,
        string $operator = '=',
        string $glue = 'AND',
        string $groupId = '0'
    ): AbstractCollection {
        if (empty($field) || !isset($value) || empty($operator)) {
            return $this;
        }

        // Convert the '=' operator to 'IN' for arrays
        if (is_array($value) && $operator === '=') {
            $operator = 'IN';
        }
        // Operator always in capital.
        $operator = trim(strtoupper($operator));
        $glue = trim(strtoupper($glue));
        $valueToBeSerialized = $value instanceof QueryInterface ? (string) $value : $value;
        $hash = md5($field . ' ' . $operator . ' ' . serialize($valueToBeSerialized) . $glue);

        // Create a group condition if not exist
        if(!isset($this->conditionGroups[$groupId])) {
            $this->conditionGroups[$groupId] = new ConditionGroup($groupId);
        }

        // The condition is already set. Do not go on.
        if ($this->conditionGroups[$groupId]->getCondition($hash, $glue) !== null) {
            return $this;
        }

        /*
        * There is resource model defined which is a Joomla BaseModel and we can use the model's state
        */
        if (isset($this->resourceModel) && $this->resourceModel instanceof BaseModel) {
            if (!in_array($operator, ['=', 'IN'])) {
                throw new \InvalidArgumentException('Operator:\'' . $operator . '\' is invalid, in the collection with a resource model');
            }
            // If the condition is new, clear the items and re-load them with the new conditions
            $this->clearItems();
            $this->resourceModel->setState($field, $value);
            $this->conditionGroups[$groupId]->setCondition($hash,'', $glue);
        }
        /*
         * No resource model. The collection defines that internally
         * In that case we have to render the conditions within the collection
         */
        else {
            if (is_array($value)) {

                if ($operator == '=' || $operator == 'IN') {
                    $operator = 'IN';
                } elseif($operator == 'LIKE') {
                    $whereLike = [];
                    foreach ($value as $val) {
                        $whereLike[] = $this->escapeName($field) . ' LIKE ' . $this->escapeValue($val.'%');
                    }
                    $value = $whereLike;
                } elseif($operator == 'BETWEEN') {
                    $whereBetween = $this->escapeName($field) . ' BETWEEN ' . $this->escapeValue($value[0]) . ' AND ' . $this->escapeValue($value[1]);
                    $value = [$whereBetween];
                }
                else {
                    $operator = 'NOT IN';
                }
                // Sanitize
                if($operator == '=' || $operator == 'IN' || $operator == 'NOT IN') {
                    $value = array_map([$this, 'escapeValue'], $value);
                    $value = '(' . implode(',', $value) . ')';
                }
            } elseif ($value instanceof QueryInterface) {
                $value = '(' . (string)$value . ')';
            }
            else {
                $value = $this->escapeValue($value);
            }

            if (!is_array($value)) {
                if($operator == 'IS NULL') {
                    $expression = $field . ' ' . $operator;
                }else {
                    $expression = $this->escapeName($field)  . ' ' . $operator . ' ' . $value;
                }
            } else {
                $expression = '(' . implode(' OR ', $value) . ')';
            }

            if ($this->conditionGroups[$groupId]->getCondition($hash, $glue) === null) {
                $this->conditionGroups[$groupId]->setCondition($hash, $expression, $glue);

                // If the condition is new, clear the items and re-load them with the new conditions
                $this->clearItems();
            }
        }

        return $this;
    }

    /**
     * Logic has to be set to the sub-classes depending on context.
     * 
     * @todo remove the db::quoteName where used and apply that in the addCondition fn
     *
     * @param   string  $fieldName
     *
     * @return string
     * @since 1.0.0
     */
    protected function escapeName(string $fieldName): string
    {
        return $fieldName;
    }

    /**
     * Logic has to be set to the sub-classes depending on context.
     *
     * @param string|array $value
     *
     * @return mixed
     * @since 1.0.0
     */
    protected function escapeValue($value)
    {
        return $value;
    }
}
