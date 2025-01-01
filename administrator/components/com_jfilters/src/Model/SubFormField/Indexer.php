<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model\SubFormField;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ConfigContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\ContextInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\SubFormField;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Class Indexer
 *
 * Indexes the Subform fields to a separate table, so that filtering is possible.
 * @package Bluecoder\Component\Jfilters\Administrator\Model\SubFormField
 */
class Indexer
{
    /**
     * The storage key in the session
     */
    const SESSION_KEY = '_jfilters.subform.state';

    /**
     * The batch size of each index batch
     */
    const BATCH_SIZE = 50;

    /**
     * @var \stdClass
     */
    protected $state;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     * @since 1.16.5
     */
    private array $itemFiledValuesDeleted = [];

    /**
     * SubFormField constructor.
     *
     * @param   LoggerInterface  $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Index the subform fields.
     *
     * @param   int           $item_id
     * @param   SubFormField  $subForm
     *
     * @return $this
     * @throws \Exception
     * @since 1.0.0
     */
    public function index(int $item_id, SubFormField $subForm): Indexer
    {
        $field = $this->getFields($subForm->getContext(), $subForm->getId(), $subForm->getName());
        if ($field && $field->type == 'subform') {
            $subfieldIds = [];
            foreach ($field->fieldparams->get('options') as $subField) {
                $subfieldIds[] = (int)$subField->customfield;
            }

            if (!empty($subForm->getSubfields())) {
                foreach ($subForm->getSubfields() as $key => &$row) {
                    // Repeatable Custom Field. Get the fields of each row
                    if (is_array($row) || is_object($row)) {
                        $row = (array)$row;
                        foreach ($row as $fieldName => &$subFieldValue) {
                            // Do not index empty values
                            if (empty($subFieldValue)) {
                                continue;
                            }

                            $fieldId = $this->getFieldId($fieldName);

                            // Check if there are nested sub-form fields (introduced in J5.2)
                            if (is_array($subFieldValue) || is_object($subFieldValue)) {
                                $subFieldValue = (array)$subFieldValue;
                                $nestedSubformField = new SubFormField($subForm->getContext(), $subFieldValue, null, $fieldId);
                                $this->index($item_id, $nestedSubformField);
                                continue;
                            }

                            if ($fieldId && in_array($fieldId, $subfieldIds) && $subFieldValue) {
                                $this->insertFieldValue($subForm->getContext(), $fieldId, $item_id, $subFieldValue);
                            }
                        }
                    } // Single entries for each sub-custom field.
                    else {
                        $subFieldValue = $row;
                        // Do not index empty values
                        if (empty($subFieldValue)) {
                            continue;
                        }
                        $fieldId = $this->getFieldId($key);
                        if ($fieldId && in_array($fieldId, $subfieldIds) && $subFieldValue) {
                            $this->insertFieldValue($subForm->getContext(), $fieldId, $item_id, $subFieldValue);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Batch index subform field values.
     *
     * @return $this
     * @throws \ReflectionException
     * @throws \Exception
     * @since 1.0.0
     */
    public function batchIndex(): Indexer
    {
        $data = $this->getState();
        $offset = $data->batchOffset;
        $items = $this->getFieldValues($offset);
        foreach ($items as $item) {
            if ($item->value) {
                $value = json_decode($item->value);
                $subform = new SubFormField($item->context, (array)$value, $item->name, $item->field_id);
                // Delete any previous value for that item. There is no way to update existing values because there are no unique indexes.
                $this->deleteItemFieldValues($item->item_id);
                $this->index($item->item_id, $subform);
            }
            $offset ++;
        }

        $data->batchOffset = $offset;
        if($data->batchOffset >= $data->total) {
            $data->complete = 1;
        }
        $this->setState($data);
        return $this;
    }

    /**
     * Method to get the indexer state.
     *
     * @return  object  The indexer state object.
     *
     * @throws \ReflectionException
     * @since   1.0.0
     */
    public function getState()
    {
        // First, try to load from the internal state.
        if ((bool) $this->state)
        {
            return $this->state;
        }

        // If we couldn't load from the internal state, try the session.
        $session = Factory::getApplication()->getSession();
        $data = $session->get(self::SESSION_KEY, null);

        // If the state is empty, load the values for the first time.
        if (empty($data))
        {
            $data = new \stdClass();
            // Set the current time as the start time.
            $data->startTime = Factory::getDate()->toSql();
            // Set the remaining default values.
            $data->batchSize   = (int) self::BATCH_SIZE;
            $data->batchOffset = 0;
            $data->total  = $this->getTotal();
            $this->setState($data);
        }

        // Set the state.
        $this->state = $data;
        return $this->state;
    }

    /**
     * Method to set the indexer state.
     *
     * @param   \stdClass  $data  A new indexer state object.
     *
     * @return  bool
     *
     * @since   1.0.0
     */
    public function setState($data)
    {
        // Check the state object.
        if (empty($data) || !$data instanceof \stdClass)
        {
            return false;
        }

        // Set the new internal state.
        $this->state = $data;

        // Set the new session state.
        Factory::getApplication()->getSession()->set(self::SESSION_KEY, $data);

        return true;
    }

    /**
     * Clear the state.
     *
     * @return bool
     * @since 1.0.0
     */
    public function clearState()
    {
        Factory::getApplication()->getSession()->set(self::SESSION_KEY, null);
        return true;
    }

    /**
     * Extract the field id from the field name.
     *
     * @param   string  $fieldName
     *
     * @return int|null
     * @since 1.0.0
     */
    protected function getFieldId(string $fieldName): ?int
    {
        $id = 0;
        // Make sure that it is field
        if (strpos($fieldName, 'field') !== false) {
            preg_match('/(\d+)$/', $fieldName, $matches);
            $id = (int)$matches[0];
        }

        return $id;
    }

    /**
     * Get the Field/s.
     *
     * @param   string       $context
     * @param   int|null     $fieldId
     * @param   string|null  $name
     *
     * @return mixed
     * @throws \Exception
     * @since 1.0.0
     */
    public function getFields($context, ?int $fieldId = null, ?string $name = null)
    {
        $fields = FieldsHelper::getFields($context, null, false, null, true);
        $filterByProperty = $fieldId ? 'id' : ($name ? 'name' : null);
        $filterByPropertyValue = $fieldId ?: ($name ?: null);
        if ($filterByProperty && $filterByPropertyValue) {
            $fieldsTmp = $fields;
            $fields = [];
            foreach ($fieldsTmp as $field) {
                if ($field->{$filterByProperty} === $filterByPropertyValue) {
                    $fields = $field;
                    break;
                }
            }
        }

        return $fields;
    }

    /**
     * @param int $offset
     *
     * @return array
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function getFieldValues($offset)
    {
        $items = [];
        $data = $this->getState();
        if($data->total > $offset) {
            /** @var ConfigContextCollection $contextConfigCollection */
            $contextConfigCollection = ObjectManager::getInstance()->getObject(ConfigContextCollection::class);
            $contextToBeIndexed = [];
            if ($contextConfigCollection->getSize() > 0) {
                /** @var ContextInterface $context */
                foreach ($contextConfigCollection as $context) {
                    $contextToBeIndexed [] = $context->getName();
                }
            }
            if ($contextToBeIndexed) {
                $fieldType = 'subform';
                /** @var DatabaseDriver $db */
                $db = Factory::getContainer()->get('DatabaseDriver');
                /** @var DatabaseQuery $query */
                $query = $db->getQuery(true);
                $query->select($query->quoteName(['fields.context', 'fields.name', 'field_values.field_id', 'field_values.item_id', 'field_values.value']))
                      ->from($query->quoteName('#__fields_values', 'field_values'))
                      ->innerJoin(
                          $query->quoteName('#__fields', 'fields'),
                          $query->quoteName('fields.id') . ' = ' . $query->quoteName('field_values.field_id')
                      )
                      ->whereIn($query->quoteName('fields.context'), $contextToBeIndexed, ParameterType::STRING)
                      ->where($query->quoteName('fields.type') . '= :field_type')
                      ->bind(':field_type', $fieldType, ParameterType::STRING);

                $db->setQuery($query, $offset, self::BATCH_SIZE);
                $items = $db->loadObjectList();
            }
        }
        return $items;
    }

    /**
     * Get the total number of records that need indexing.
     *
     * @return int|mixed|null
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function getTotal()
    {
        $total = 0;

        /** @var ConfigContextCollection $contextConfigCollection */
        $contextConfigCollection = ObjectManager::getInstance()->getObject(ConfigContextCollection::class);
        $contextToBeIndexed = [];
        if ($contextConfigCollection->getSize() > 0) {
            /** @var ContextInterface $context */
            foreach ($contextConfigCollection as $context) {
                $contextToBeIndexed [] = $context->getName();
            }
        }
        if ($contextToBeIndexed) {
            $fieldType = 'subform';
            /** @var DatabaseDriver $db */
            $db = Factory::getContainer()->get('DatabaseDriver');
            /** @var DatabaseQuery $query */
            $query = $db->getQuery(true);
            $query->select('COUNT(*)')
                  ->from($query->quoteName('#__fields_values', 'field_values'))
                  ->innerJoin(
                      $query->quoteName('#__fields', 'fields'),
                      $query->quoteName('fields.id') . ' = ' . $query->quoteName('field_values.field_id')
                  )
                  ->whereIn($query->quoteName('fields.context'), $contextToBeIndexed, ParameterType::STRING)
                  ->where($query->quoteName('fields.state') . '= 1')
                  ->where($query->quoteName('fields.type') . '= :field_type')
                  ->bind(':field_type', $fieldType, ParameterType::STRING);

            $db->setQuery($query);

            $total = $db->loadResult();
        }

        return $total;
    }


    /**
     * Delete field values by itemId and fieldIds
     *
     * @param   int    $itemId
     *
     * @since 1.0.0
     */
    public function deleteItemFieldValues(int $itemId)
    {
        if (empty($this->itemFiledValuesDeleted[$itemId])) {
            /** @var DatabaseDriver $db */
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);

            $query->delete($query->quoteName('#__jfilters_fields_subform_values'))
                ->where($query->quoteName('item_id') . ' = :itemid')
                ->bind(':itemid', $itemId, ParameterType::INTEGER);
            $db->setQuery($query)->execute();
            $this->itemFiledValuesDeleted[$itemId] = true;
        }
    }

    /**
     * Setting the value for the given field id, context and item id.
     *
     * @param   string  $context  The context
     * @param   string  $fieldId  The field ID.
     * @param   string  $itemId   The ID of the item.
     * @param   string  $value    The value.
     *
     * @return  bool
     *
     * @since   1.0.0
     */
    public function insertFieldValue(string $context, int $fieldId, $itemId, $value)
    {
        /** @var DatabaseDriver $db */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $value = (array)$value;
        $newObj = new \stdClass();

        $newObj->field_id = (int)$fieldId;
        $newObj->item_id = $itemId;

        foreach ($value as $v) {
            $newObj->value = $v;

            $db->insertObject('#__jfilters_fields_subform_values', $newObj);
        }

        return true;
    }
}