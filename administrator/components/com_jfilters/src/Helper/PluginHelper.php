<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Helper;

\defined('_JEXEC') or die();

use Joomla\CMS\Factory;

class PluginHelper
{
	/**
	 * @var array
	 */
	protected static $plugins = [];

	/**
	 * Get the plugin.
	 *
	 * @param string $group
	 * @param string $name
	 *
	 * @return \stdClass|null
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public static function getPlugin($group, $name)
	{
		$key = $group . '/' . $name;

		if (!isset(self::$plugins[$key]))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
				->where($db->quoteName('folder') . ' = ' . $db->quote($group))
				->where($db->quoteName('element') . ' = ' . $db->quote($name));
			$db->setQuery($query);

			try
			{
				self::$plugins[$key] = $db->loadObject();
			}
			catch (\RuntimeException $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}
		return self::$plugins[$key];
	}

	/**
	 * Get the plugin id.
	 *
	 * @param string $group
	 * @param string $name
	 *
	 * @return int
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public static function getPluginId($group, $name)
	{
		$plugin = self::getPlugin($group, $name);
		return isset($plugin) ? $plugin->extension_id : 0;
	}
}