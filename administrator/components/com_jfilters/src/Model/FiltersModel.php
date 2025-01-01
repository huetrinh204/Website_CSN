<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Model;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Filter\PropertyHandler;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Class FiltersModel has the role of resource Model
 * @package Bluecoder\Component\Jfilters\Administrator\Model
 */
Class FiltersModel extends ListModel
{
    /**
     * @var ObjectManager
     * @since   1.0.0
     */
    protected $objectManager;

    /**
     * @var PropertyHandler
     */
    protected $propertyHandler;

    /**
     * FiltersModel constructor.
     * @param array $config
     * @param MVCFactoryInterface|null $factory
     * @throws \Exception
     */
    public function __construct($config = array(), MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'a.id',
                'name',
                'a.name',
                'config_name',
                'a.config_name',
                'label',
                'a.label',
                'alias',
                'a.alias',
                'display',
                'a.display',
                'context',
                'a.context',
                'state',
                'a.state',
                'access',
                'a.access',
                'root',
                'a.root',
                'language',
                'a.language',
                'ordering',
                'a.ordering',
                'checked_out',
                'a.checked_out',
                'checked_out_time',
                'a.checked_out_time',
                'created_time',
                'a.created_time',
                'Itemid'
            ];
        }
        $this->objectManager = ObjectManager::getInstance();
        $this->propertyHandler = $this->objectManager->getObject(PropertyHandler::class);
        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param string $ordering
     * @param string $direction
     * @throws \Exception
     * @since   1.0.0
     */
    protected function populateState($ordering = 'a.ordering', $direction = 'asc')
    {
        $app = Factory::getApplication();
        $formSubmited = $app->input->post->get('form_submited');
        $forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');
        $layout = $app->input->get('layout', '', 'cmd');

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $id = $this->getUserStateFromRequest($this->context . '.filter.id', 'filter_id');
        $this->setState('filter.id', $id);

        $parent_id = $this->getUserStateFromRequest($this->context . '.filter.parent_id', 'filter_parent_id');
        $this->setState('filter.parent_id', $parent_id);

        $name = $this->getUserStateFromRequest($this->context . '.filter.name', 'filter_name');
        $this->setState('filter.name', $name);

        $config_name = $this->getUserStateFromRequest($this->context . '.filter.config_name', 'filter_config_name');
        $this->setState('filter.config_name', $config_name);

        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '');
        $this->setState('filter.state', $state);

        $root = $this->getUserStateFromRequest($this->context . '.filter.root', 'filter_root', '');
        $this->setState('filter.root', $root);

        $context = $this->getUserStateFromRequest($this->context . '.filter.context', 'filter_context');
        $this->setState('filter.context', $context);

        $display = $this->getUserStateFromRequest($this->context . '.filter.display', 'filter_display');
        $this->setState('filter.display', $display);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        // Set the ItemId. Used in modal filters
        $additionalFilters = $app->getInput()->get('additional');
        $itemId = !empty($additionalFilters['Itemid']) && $layout == 'modal' ? $additionalFilters['Itemid'] : '';
        $app->setUserState($this->context . '.additional.Itemid', $itemId);
        $this->setState('additional.Itemid', $itemId);


        if ($formSubmited) {
            $access = $app->getInput()->post->get('access');
            $this->setState('filter.access', $access);

            $authorId = $app->getInput()->post->get('author_id');
            $this->setState('filter.author_id', $authorId);
        }

        // List state information.
        parent::populateState($ordering, $direction);

        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
        }
    }

    /**
     * Method to get a store id based on the model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param string $id An identifier string to generate the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.0.0
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.id');
        $id .= ':' . $this->getState('filter.parent_id');
        $id .= ':' . $this->getState('filter.name');
        $id .= ':' . $this->getState('additional.Itemid');
        $id .= ':' . serialize($this->getState('filter.config_name'));
        $id .= ':' . serialize($this->getState('filter.state'));
        $id .= ':' . serialize($this->getState('filter.context'));
        $id .= ':' . serialize($this->getState('filter.display'));
        //could be an array
        $id .= ':' . serialize($this->getState('filter.language'));

        return parent::getStoreId($id);
    }

    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return  QueryInterface   A JDatabaseQuery object to retrieve the data set.
     *
     * @throws \Exception
     * @since   1.0.0
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $app = Factory::getApplication();
        $user = $app->getIdentity();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.parent_id, a.config_name, a.name, a.context, a.label, a.alias, a.display, a.state, a.access' .
                ', a.root, a.ordering, a.checked_out, a.checked_out_time, a.created_time, a.updated_time, a.language'
            )
        );
        $query->select($db->quoteName('a.attribs', 'attributes'))
            ->from($db->quoteName('#__jfilters_filters', 'a'));

        // Only in admin or in editor (can be front-end)
        if ($app->isClient('administrator') || ($app->getInput()->getCmd('layout') == 'modal' && !empty($app->getInput()->getCmd('editor')))) {
            // Join over the language.
            $query->select($db->quoteName('l.title', 'language_title'))
                ->select($db->quoteName('l.image', 'language_image'))
                ->join('LEFT', $db->quoteName('#__languages') . ' AS l ON a.language=l.lang_code');

            // Join over the users for the checked out user.
            $query->select($db->quoteName('uc.name', 'editor'))->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

            // Join over the access groups.
            $query->select($db->quoteName('ag.title') . ' AS access_level')
                ->join('LEFT', $db->quoteName('#__viewlevels') . ' AS ag ON ag.id = a.access');

            // Search in title or label
            $search = $this->getState('filter.search');

            if (!empty($search)) {
                if (stripos($search, 'id:') === 0) {
                    $query->where($db->quoteName('a.id') . '= ' . (int)substr($search, 3));
                } elseif (stripos($search, 'parent_id:') === 0) {
                    $query->where($db->quoteName('a.parent_id') . '= ' . (int)substr($search, 10));
                } else {
                    $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                    $query->where('(a.name LIKE ' . $search . ' OR a.label LIKE ' . $search . ')');
                }
            }

            // Search by the language of the menu item.
            $itemId = $this->getState('additional.Itemid');

            if(!empty($itemId)) {
                $menu = $app->getMenu('site');
                /** @var MenuItem $menuItem */
                $menuItem = $menu->getItem($itemId);
                
                // Check the menu item's language and return only filters for that language
                if($menuItem->language != '*') {
                    $query->where($db->quoteName('a.language') . '= :language')
                    ->bind(':language', $menuItem->language, ParameterType::STRING);
                }
            }
        }

        /**
         * Implement View Level Access
         * But the user can be null, if we run a cli script (e.g. unit tests)
         */
        if ($user !== null && (!$app->isClient('administrator') || !$user->authorise('core.admin'))) {
            $groups = $user->getAuthorisedViewLevels();
            $query->whereIn($db->quoteName('a.access'), $groups);
        }

        /**
         * Filter by id
         * This is used by other parts of the component to fetch a filter by it's id
         */
        if ($id = $this->getState('filter.id')) {
            $query->where($db->quoteName('a.id') . '= :id')
                ->bind(':id', $id, ParameterType::INTEGER);
        }

        /**
         * Filter by parent_id
         * This is used by other parts of the component
         */
        if ($parent_id = $this->getState('filter.parent_id')) {
            $query->where($db->quoteName('a.parent_id') . '= :parent_id')
                ->bind(':parent_id', $parent_id, ParameterType::INTEGER);
        }

        /**
         * Filter by name
         * This is used by other parts of the component to fetch a filter by it's name
         */
        if ($name = $this->getState('filter.name')) {
            $query->where($db->quoteName('a.name') . '= :name')
                ->bind(':name', $name);
        }

        /**
         * Filter by config_name
         * This is used by other parts of the component
         */
        if ($config_name = $this->getState('filter.config_name')) {
            if (is_array($config_name)) {
                $config_name = ArrayHelper::toString($config_name);
                $query->whereIn($db->quoteName('a.config_name'), $config_name);
            }else {
                $query->where($db->quoteName('a.config_name') . '= :config_name')
                      ->bind(':config_name', $config_name);
            }
        }

        // Filter by state
        $state = $this->getState('filter.state');

        if (is_array($state)) {
            $state = ArrayHelper::toInteger($state);
            $query->whereIn($db->quoteName('a.state'), $state, ParameterType::INTEGER);
        } elseif (is_numeric($state)) {
            $query->where($db->quoteName('a.state') . '= :state ')
                  ->bind(':state', $state, ParameterType::INTEGER);
        } elseif (!$state) {
            $query->whereIn($db->quoteName('a.state'), [0, 1, 2]);
        }

        /**
         * Filter by root
         * This is used by other parts of the component
         */
        if ($root = $this->getState('filter.root')) {
            $query->where($db->quoteName('a.root') . '= :root')
                  ->bind(':root', $root);
        }

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            if (is_array($access)) {
                $access = ArrayHelper::toInteger($access);
                $query->whereIn($db->quoteName('a.access'), $access, ParameterType::INTEGER);
            } else {
                $query->where($db->quoteName('a.access') . '= :access')
                    ->bind(':access', $access, ParameterType::INTEGER);
            }
        }

        // Filter by context
        if ($context = $this->getState('filter.context')) {
            if (is_array($context)) {
                $context = ArrayHelper::toString($context);
                $query->whereIn($db->quoteName('a.context'), $context);
            } else {
                $query->where($db->quoteName('a.context') . '= :context')
                      ->bind(':context', $context);
            }
        }

        // Filter by display
        if ($display = $this->getState('filter.display')) {
            if (is_array($display)) {
                $display = ArrayHelper::toString($display);
                $query->whereIn($db->quoteName('a.display'), $display);
            } else {
                $query->where($db->quoteName('a.display') . '= :display')
                      ->bind(':display', $display);
            }
        }

        // Filter by language
        if ($language = $this->getState('filter.language')) {
            if (!is_array($language)) {
                $language = [$language];
            }
            $query->whereIn($db->quoteName('a.language'), $language, ParameterType::STRING);
        }

        // Add the list ordering clause
        $listOrdering = $this->state->get('list.ordering', $this->getDefaultOrderingField());
        $orderDirn = $this->state->get('list.direction', 'ASC');
        $query->order($db->escape($listOrdering) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    /**
     * Get the default ordering field
     *
     * @return string
     * @since   1.0.0
     */
    public function getDefaultOrderingField()
    {
        return 'a.ordering';
    }

    /**
     * Gets an array of objects from the results of database query.
     *
     * @param string $query The query.
     * @param integer $limitstart Offset.
     * @param integer $limit The number of records.
     *
     * @return  array  An array of results.
     *
     * @throws  \RuntimeException
     * @since   1.0.0
     */
    protected function _getList($query, $limitstart = 0, $limit = 0)
    {
        $result = parent::_getList($query, $limitstart, $limit);
        $componentParams = ComponentHelper::getParams('com_jfilters', true);
        if (is_array($result)) {
            foreach ($result as $item) {

                // we do not want to alter the original component params, hence clone.
                $componentParams_tmp = clone $componentParams;
                $item->attributes = $componentParams_tmp->merge(new Registry($item->attributes));

                // Sanitize the attributes
                $attributesTmp = $this->propertyHandler->getArray($item->attributes->toArray());
                $item->attributes = new Registry($attributesTmp);
                $item = $this->setSanitizedProperties($item);
            }
        }

        return $result;
    }

    /**
     * Sanitize properties
     *
     * @param $item
     *
     * @return mixed
     * @since 1.0.0
     */
    protected function setSanitizedProperties($item)
    {
        foreach (get_object_vars($item) as $varName => $varValue) {
            $item->$varName = $this->propertyHandler->get($varName, $varValue);
        }

        return $item;
    }
}
