<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       16.11.15
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
Jloader::register('MatukioHelperRoute', JPATH_ADMINISTRATOR . '/components/com_matukio/helpers/util_route.php');

/**
 * Class CcommentComponentMatukioPlugin
 *
 * @since  2.0
 */
class CcommentComponentMatukioPlugin extends ccommentComponentPlugin
{
	/**
	 * With this function we determine if the comment system should be executed for this
	 * content Item
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		$config = ccommentConfig::getConfig('com_matukio');
		$row = $this->row;

		$contentIds = $config->get('basic.exclude_content_items', array());
		$categories = $config->get('basic.categories', array());
		$include = $config->get('basic.include_categories', 0);

		/* content ids */
		if (count($contentIds) > 0)
		{
			$result = in_array((($row->id == 0) ? -1 : $row->id), $contentIds);

			if ($include && $result)
			{
				return true; /* include and selected */
			}

			if (!$include && $result)
			{
				return false; /* exclude and selected */
			}
		}

		/* categories */
		$result = in_array((($row->catid == 0) ? -1 : $row->catid), $categories);

		if ($include && $result)
		{
			return true; /* include and selected */
		}

		if (!$include && $result)
		{
			return false; /* exclude and selected */
		}

		if (!$include)
		{
			return true; /* was not excluded */
		}

		return false;
	}

	/**
	 * This function decides whether to show the comments
	 * in an article/item or to show the readmore link
	 *
	 * If it returns true - the comments are shown
	 * If it returns false - the setShowReadon function will be called
	 *
	 * @return boolean
	 */
	public function isSingleView()
	{
		$input = JFactory::getApplication()->input;
		$option = $input->getCmd('option', '');
		$view = $input->getCmd('view', '');


		return ($option == 'com_matukio'
			&& $view == 'event'
		);
	}

	/**
	 * This function determines whether to show the comment count or not
	 *
	 * @return bool
	 */
	public function showReadOn()
	{
		$config = ccommentConfig::getConfig('com_matukio');
		$params = $this->params;
		$readOn = $config->get('layout.show_readon', 0);
		$readMore = false;
		$linkTitles = false;

		if ($params != null)
		{
			$readMore = $params->get('show_readmore', 0);
			$linkTitles = $params->get('link_titles', 0);
		}

		if ($config->get('layout.menu_readon') && !$readMore)
		{
			$readOn = false;
		}

		if ($config->get('layout.intro_only') && $linkTitles)
		{
			$readOn = false;
		}

		return $readOn;
	}

	/**
	 * Create a link to the Matukio event
	 *
	 * @param   int        $contentId  - the matukio event id
	 * @param   int        $commentId  - the comment id
	 * @param   bool|true  $xhtml      - kind of link?
	 *
	 * @return mixed
	 */
	public function getLink($contentId, $commentId = 0, $xhtml = true)
	{
		$add = '';

		// If we have a row - use the info in it
		if (isset($this->row))
		{
			$link = MatukioHelperRoute::getEventLink($this->row);
		}
		else
		{
			Jloader::register('MatukioModelEvent', JPATH_SITE . '/components/com_matukio/models/event.php');

			$emodel = JModelLegacy::getInstance('Event', 'MatukioModel');
			$event = $emodel->getItem($contentId);
			$link = MatukioHelperRoute::getEventLink($event);
		}

		if ($commentId)
		{
			$add = "#ccomment-comment=$commentId";
		}

		$url = JRoute::_($link . $add, $xhtml);

		return $url;
	}

	/**
	 * Returns the id of the author of an item
	 *
	 * @param   int  $contentId  - the matukio repeating event id
	 *
	 * @return mixed
	 */
	public function getAuthorId($contentId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('m.publisher as created_by')->from('#__matukio_recurring AS r')
				->leftJoin('#__matukio AS m ON r.event_id = m.id')
			->where('r.id = ' . $db->q($contentId));

		$db->setQuery($query, 0, 1);

		$author = $db->loadObject();

		if ($author)
		{
			return $author->created_by;
		}

		return false;
	}

	/**
	 * Get the Matukio Event title
	 *
	 * @param   array  $ids  - the event ids
	 *
	 * @return mixed
	 *
	 * return object
	 */
	public function getItemTitles($ids)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.id as id, m.title as title')->from('#__matukio_recurring AS r')
			->leftJoin('#__matukio AS m ON r.event_id = m.id')
			->where('r.id IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		return $db->loadObjectList('id');
	}
}
