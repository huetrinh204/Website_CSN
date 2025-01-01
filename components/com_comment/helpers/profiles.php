<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       01.01.15
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class CcommentHelperProfiles
 *
 * @since  5.3
 */
class CcommentHelperProfiles
{
	/**
	 * makes a profile link any of the supported systems
	 *
	 * @param   int     $id    -  user id
	 * @param   string  $type  -  the profile system type
	 *
	 * @return string - html link to profile or just the user name if id is missing
	 */
	public static function profileLink($id, $type)
	{
		if ((int) $id == 0 || !$type)
		{
			return '';
		}

		$profileSystem = CompojoomProfiles::getInstance($type);

		return $profileSystem->getLink($id);
	}
}
