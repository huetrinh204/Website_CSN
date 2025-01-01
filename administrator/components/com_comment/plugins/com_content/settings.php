<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       18.02.13
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class ccommentComponentContentSettings
 *
 * @since  4.0
 */
class CcommentComponentContentSettings extends CcommentComponentSettings
{
	/**
	 * categories option list used to display the include/exclude category list in setting
	 * must return an array of objects (id,title)
	 *
	 * @return array() - associative array (id, title)
	 */
	public function getCategories()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.level, a.parent_id')
			->from('#__categories AS a')
			->where('a.parent_id > 0');

		// Filter on extension.
		$query->where('extension = ' . $db->quote('com_content'));
		$query->where('a.published = ' . $db->quote(1));

		$query->order('a.lft');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		return $this->nestCategoties($items);
	}
}
