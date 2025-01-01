<?php
/**
 * @package    CComment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       24.07.15
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class ccommentComponentHotspotsPlugin
 *
 * @since  4.0
 */
class CcommentComponentHotspotsPlugin extends CcommentComponentPlugin
{
	/**
	 * With this function we determine if the comment system should be executed for this
	 * content Item
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		$config = ccommentConfig::getConfig('com_hotspots');
		$row    = $this->row;

		$contentIds = $config->get('basic.exclude_content_items', array());
		$categories = $config->get('basic.categories', array());
		$include    = $config->get('basic.include_categories', 0);

		/* doc id excluded ? */
		if (in_array((($row->id == 0) ? -1 : $row->id), $contentIds))
		{
			return false;
		}

		/* category included or excluded ? */
		$result = in_array((($row->catid == 0) ? -1 : $row->catid), $categories);

		if (($include && !$result) || (!$include && $result))
		{
			return false; /* include and not found OR exclude and found */
		}

		return true;
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
		$input  = JFactory::getApplication()->input;
		$option = $input->getCmd('option', '');
		$view   = $input->getCmd('view', '');

		return ($option == 'com_hotspots'
			&& $view == 'hotspot'
		);
	}

	/**
	 * This function determines whether to show the comment count or not
	 *
	 * @return bool
	 */
	public function showReadOn()
	{
		$config = ccommentConfig::getConfig('com_hotspots');

		return $config->get('layout.show_readon');
	}

	/**
	 * Creates a link to single view of a hotspot
	 *
	 * @param   int   $contentId  - the hotspot id
	 * @param   int   $commentId  - the comment id
	 * @param   bool  $xhtml      - whether or not we should generate a xhtml link
	 *
	 * @return string|void
	 */
	public function getLink($contentId, $commentId = 0, $xhtml = true)
	{
		$add = '';

		if ($commentId)
		{
			$add = "#ccomment-comment=$commentId";
		}

		if (isset($this->row))
		{
			$catid   = $this->row->catid;
			$catname = $this->row->cat_name;
			$name    = $this->row->title;
		}
		else
		{
			$catInfo = $this->getCategoryInfo($contentId);
			$catid   = $catInfo->id;
			$catname = $catInfo->title;
			$names   = $this->getItemTitles(array($contentId));
			$name    = $names[$contentId]->title;
		}

		$url = JRoute::_('index.php?option=com_hotspots&view=hotspot' .
			'&catid=' . $catid . ':' . JFilterOutput::stringURLSafe($catname) .
			'&id=' . $contentId . ':' . JFilterOutput::stringURLSafe($name) .
			'&Itemid=' . ccommentHelperUtils::getItemid('com_hotspots') .
			$add, $xhtml
		);

		return $url;
	}

	/**
	 * Returns information about the category of the hotspot
	 *
	 * @param   int  $id  - the id of the hotspot
	 *
	 * @return mixed
	 */
	private function getCategoryInfo($id)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT c.id, c.title FROM ' . $db->qn('#__categories') . ' AS c'
			. ' LEFT JOIN ' . $db->qn('#__hotspots_marker') . ' AS m'
			. ' ON m.catid = c.id'
			. ' WHERE m.id = ' . $db->Quote($id);
		$db->setQuery($query, 0, 1);

		return $db->loadObject();
	}

	/**
	 * Returns the id of the author of an item
	 *
	 * @param   int  $contentId  - the article id
	 *
	 * @return mixed
	 */
	public function getAuthorId($contentId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('created_by')->from('#__hotspots_marker')
			->where('id = ' . $db->q($contentId));

		$db->setQuery($query, 0, 1);
		$author = $db->loadObject();

		if ($author)
		{
			return $author->created_by;
		}

		return false;
	}

	/**
	 * Load the article titles
	 *
	 * @param   array  $ids  - array with ids
	 *
	 * @return mixed
	 */
	public function getItemTitles($ids)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, title')->from('#__hotspots_marker')
			->where('id IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		return $db->loadObjectList('id');
	}
}
