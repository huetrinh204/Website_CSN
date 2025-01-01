<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Field\DisplaytypesField;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\PropertyHandler;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Bluecoder\Component\Jfilters\Site\Service\Router\Rules\FilterRules;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Class FilterModel
 *
 * The Joomla Model
 *
 * @package Bluecoder\Component\Jfilters\Administrator\Model
 */
class FilterModel extends AdminModel
{
    /**
     * Abstract method for getting the form from the model.
     *
     * @param array $data
     * @param bool $loadData
     * @return bool|\Joomla\CMS\Form\Form
     * @throws \Exception
     * @since 1.0.0
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_jfilters.filter', 'filter', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object)$data)) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Save the supplied items
     *
     * @param array $filters
     * @return array
     * @throws \Exception
     * @since 1.0.0
     */
    public function saveBatch(array $filters): array
    {
        $savedIds = [];
        static $ordering = 0;

        foreach ($filters as $filter) {
            /** @var  Collection $filtersCollection */
            $filtersCollection = ObjectManager::getInstance()->getObject(Collection::class);
            $filtersCollection->clear();
            $filtersCollection->addCondition('filter.config_name',
                $filter->config_name)->addCondition('filter.parent_id',
                $filter->parent_id)->addCondition('filter.language', $filter->language);

            if ($filtersCollection->getSize() > 0) {
                /** @var FilterInterface $existingFilter */
                foreach ($filtersCollection as $existingFilter) {
                    $data = $existingFilter->getData();
                    $filterTmp = $filter;
                    $filterTmp->id = $existingFilter->getId();
                    $filterTmp->state = $existingFilter->getState();
                    $filterTmp->display = $existingFilter->getDisplay();
                    $filterTmp->label = $existingFilter->getLabel();
                    $filterTmp->alias = $data->alias;
                    $filterTmp->root = (int)$existingFilter->getRoot();
                    $filterTmp->ordering = $data->ordering;
                    // Set the last existing ordering
                    $ordering = $data->ordering ++;
                    $filterTmp->attribs = $existingFilter->getAttributes()->toString();
                    $filterTmp->access = $data->access;
                    $savedIds[] = $this->save($filterTmp);
                }
            } else {
                $filter->ordering = $ordering;
                $savedIds[] = $this->save($filter);
                $ordering ++;
            }
        }
        return array_filter($savedIds);
    }

    /**
     * Delete other than the supplied ids
     *
     * @param array $filterIds
     * @return FilterModel
     * @throws \Exception
     * @since 1.0.0
     */
    public function deleteBatchOtherThan(array $filterIds): FilterModel
    {
        if (empty($filterIds)) {
            return $this;
        }
        $query = $this->_db->getQuery(true)
            ->delete()->from($this->getTable()->getTableName())->where('id NOT IN(' .
                implode(',',
                    $filterIds) . ')');
        $this->_db->setQuery($query);
        $this->_db->execute();

        $objectManager = ObjectManager::getInstance();
        /** @var  LoggerInterface $logger */
        $logger = $objectManager->getObject(LoggerInterface::class);
        $logger->info('Filters other than generated, were deleted');
        return $this;
    }

    /**
     * Batch copy
     *
     * @param int $category
     * @param array $ids
     * @param array $contexts
     * @return array
     * @throws \Exception
     * @since 1.0.0
     */
    public function batchCopy($category, $ids, $contexts = [])
    {
        $newIds = [];
        $table = $this->getTable();
        foreach ($ids as $id) {
            $table->reset();

            try {
                // Check that the row actually exists
                $table->load($id);
            } catch (\Exception $exception) {
                // suck it and go next
                continue;
            }
            $labelField = $table->getColumnAlias('label');
            $aliasField = $table->getColumnAlias('alias');

            $table->$labelField = StringHelper::increment($table->$labelField);
            $table->$aliasField = StringHelper::increment($table->$aliasField, 'dash');

            // Reset the ID because we are making a copy
            $table->id = 0;
            $table->state = 0;

            try {
                $table->check();
                $table->store();
            } catch (\Exception $exception) {
                // suck it and go next
                continue;
            }

            $newIds [] = $table->get('id');
        }
        return $newIds;
    }

    /**
     * @return bool
     * @since 1.0.0
     */
    public function purge()
    {
        $this->_db->truncateTable('#__jfilters_filters');
        return true;
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Exception
     * @since 1.0.0
     */
    public function save($data)
    {
        if (is_object($data)) {
            $data = (array)$data;
        }
        $id = 0;
        // we need to reset it. Otherwise, it loads the previous one.
        $this->setState('filter.id', null);
        $data['alias'] = $this->generateAlias($data);
        /** @var  LoggerInterface $logger */
        $logger = ObjectManager::getInstance()->getObject(LoggerInterface::class);

        if($data['alias'] === false || !$saved = parent::save($data)) {
            $logger->error($this->getError());
        } else {
            $id = $this->getState('filter.id');
            $logger->info(sprintf('Filter with id: %d, saved.', $id));
        }

        return $id;
    }

    /**
     * Method to generate an alias
     *
     * @param $filter
     * @return string
     * @throws \Exception
     * @since 1.0.0
     */
    protected function generateAlias($filter)
    {
        $filter = (object)$filter;
        $filter->alias = $filter->alias ?? '';
        $alias = OutputFilter::stringURLUnicodeSlug($filter->alias);
        $empty = false;

        if (empty($alias)) {
            $empty = true;
            //generate from name
            if (Factory::getApplication()->get('unicodeslugs') == 1) {
                $alias = OutputFilter::stringURLUnicodeSlug($filter->label);
            } else {
                $alias = OutputFilter::stringURLSafe($filter->label);
            }
        }

        // Throw an error, if the set alias uses a reserved var name.
        if (in_array($alias, FilterRules::RESERVED_QUERY_VAR_NAMES)) {

            if($empty === false) {
                // If it is a user input throw an error.
                $this->setError(Text::sprintf('COM_JFILTERS_ERROR_ALIAS_RESERVED', $alias, $alias));
                return false;
            } else {
                // If it is created by the script, just increment it.
                $alias = StringHelper::increment($alias, 'dash');
            }
        }

        // Alter the alias if exist
        $table = $this->getTable();
        while ($table->load(['alias' => $alias])) {
            // The filter is using the same alias as before.
            if (!empty($filter->id) && $table->id == $filter->id) {
                break;
            }

            $alias = StringHelper::increment($alias, 'dash');
        }

        return $alias;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return array|mixed
     * @throws \Exception
     * @since 1.0.0
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app = Factory::getApplication();
        $data = $app->getUserState('com_jfilters.edit.filter.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Prime some default values.
            if ($this->getState('filter.id') == 0) {
                $filters = (array)$app->getUserState('com_jfilters.filter.filter');

            }
        }
        $this->preprocessData('com_jfilters.filter', $data);

        return $data;
    }


    /**
     * Method to get a single record.
     *
     * @param integer $pk The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     * @since 1.0.0
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
            $registry = new Registry($item->attribs);
            $item->attribs = $registry->toArray();
        }
        return $item;
    }

    /**
     * Prepare and sanitise the table data prior to saving.
     * Used to set default values to the columns.
     *
     * @param Table $table
     * @throws \Exception
     * @since 1.0.0
     */
    protected function prepareTable($table)
    {
        /** @var  PropertyHandler $propertyHandler */
        $propertyHandler = ObjectManager::getInstance()->getObject(PropertyHandler::class);
        $app = Factory::getApplication();

        //generate label from title if empty
        $table->label = $table->label ?? $table->name;

        // Get the proper display after applying the property rules
        $table->display = $propertyHandler->get('display', $table->display ?? DisplaytypesField::DEFAULT_DISPLAY_TYPE);
        $table->language = empty($table->language) ? '*' : $table->language;
        // set updated_time only if is created
        $table->updated_time = !empty($table->created_time) ? Factory::getDate()->toSql() : null;
        $table->created_time = empty($table->created_time) ? Factory::getDate()->toSql() : $table->created_time;
        $table->checked_out = $app->getIdentity()->id;
        $table->attribs = empty($table->attribs) ? '{}' : $table->attribs;

        // Sanitize the attributes
        $attributesTmp = new Registry($table->attribs);
        $attributesTmp = $propertyHandler->getArray($attributesTmp->toArray());
        $attributesTmp = new Registry($attributesTmp);
        $table->attribs = $attributesTmp->toString();

        $table->root = (int) $table->root;
    }
}
