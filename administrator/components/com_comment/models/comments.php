<?php
/**
 * @package    CComment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       11.06.15
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

/**
 * Class ccommentModelComments
 *
 * @since  3.0
 */
class CcommentModelComments extends JModelList
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		$component = $app->getUserStateFromRequest($this->context . '.component', 'component');
		$this->setState('filter.component', $component);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// List state information.
		parent::populateState('date', 'DESC');
	}

	/**
	 * Creates the list query
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('c.*, u.name as uname, u.username')->from('#__comment AS c');
		$query->leftJoin('#__users as u ON c.userid = u.id');
		$search = $this->getState('filter.search');

		if ($search)
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');

			$columns = array(
				'c.comment',
				'c.email',
				'c.name',
				'c.importtable',
				'u.username',
				'u.name'
			);

			$likeFunc = function($value) use ($search)
			{
				return $value . ' LIKE ' . $search;
			};

			$query->where('(' .	implode(' OR ', array_map($likeFunc, $columns)) . ')');
		}

		$component = $this->getState('filter.component');

		if ($component)
		{
			$query->where('c.component=' . $db->q($component));
		}

		$published = $this->getState('filter.published');


		if (is_numeric($published))
		{
			$query->where('c.published=' . $db->q($published));
		}

		$orderCol  = $this->state->get('list.ordering', 'c.date');
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Get a single comment
	 *
	 * @param   int  $id  - the id of the comment
	 *
	 * @return mixed
	 */
	public function getComment($id)
	{
		$database = JFactory::getDBO();
		$query    = 'SELECT * FROM ' . $database->qn('#__comment')
			. ' WHERE id = ' . $database->Quote($id);

		$database->setQuery($query);
		$comment = $database->loadObject();

		return $comment;
	}
}
