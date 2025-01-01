<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       11.07.16
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class CcommentComponentSettings
 *
 * @since  5.3
 */
abstract class CcommentComponentSettings
{
	/**
	 * The application configuration object.
	 *
	 * @since  1.0
	 */
	protected static $items;

	/**
	 * Categories option list used to display the include/exclude category list in setting
	 * must return an array of objects (id,title)
	 *
	 * @return array
	 */
	public function getCategories()
	{
		$options = array();

		return $options;
	}

	/**
	 * Creates a nested array of categories
	 *
	 * @param   items  $items  The name of the array.
	 *
	 * @return array with objects
	 */
	public function nestCategoties($items)
	{
		static::$items = array();

		foreach ($items as &$item)
		{
			$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
			$item->title = str_repeat('- ', $repeat) . $item->title;
			static::$items[] = JHtml::_('select.option', $item->id, $item->title);
		}

		return static::$items;
	}
}
