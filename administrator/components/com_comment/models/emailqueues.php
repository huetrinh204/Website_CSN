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
 * Class ccommentModelEmailQueue
 *
 * @since  5.0
 */
class CcommentModelEmailQueues extends JModelList
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
		$query->select('*')->from('#__comment_queue AS c');
		$search = $this->getState('filter.search');
		$ids = $this->getState('ids');
		if ($search)
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');

			$columns = array(
				'c.id',
				'c.mailfrom',
				'c.fromname',
				'c.recipient',
				'c.subject',
				'c.created'
			);

			$likeFunc = function($value) use ($search)
			{
				return $value . ' LIKE ' . $search;
			};

			$query->where('(' .	implode(' OR ', array_map($likeFunc, $columns)) . ')');
		}

		if (is_array($ids)&& count($ids))
		{
			$query->where(CompojoomQueryHelper::in('id', $ids, $db));
		}

		return $query;

	}
}
