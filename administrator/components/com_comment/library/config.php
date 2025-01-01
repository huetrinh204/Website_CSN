<?php
/**
 * @package    CComment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       22.10.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');
use Joomla\String\StringHelper;

/**
 * Class ccommentConfig
 *
 * @since  5.0
 */
class ccommentConfig
{
	protected $component = null;

	private static $instances = array();

	/**
	 * The Constructor
	 */
	protected function __construct()
	{
	}

	/**
	 * A new instance of the class has to be generated with getConfig
	 * that is why we forbid clonning here
	 *
	 * @return void
	 */
	protected function __clone()
	{
	}

	/**
	 * Creates a configuration object for the specified component
	 *
	 * @param   string  $component  - the component name
	 *
	 * @return mixed
	 */
	public static function getConfig($component)
	{
		if (!isset(self::$instances[$component]))
		{
			JPluginHelper::importPlugin('compojoomcomment');
            $appl = JFactory::getApplication();

			$config = self::_createConfig($component);

			// Allow plugins to modify the configuration
            $appl->triggerEvent('onPrepareConfig', array('com_comment.config', &$config));

			self::$instances[$component] = $config;
		}

		return self::$instances[$component];
	}

	/**
	 * Creates the config object
	 *
	 * @param   string  $component  - the component name
	 *
	 * @return JRegistry
	 *
	 * @throws Exception
	 */
	private static function &_createConfig($component)
	{
		$database = JFactory::getDBO();
		$query = $database->getQuery(true);

		$query->select('*')->from($database->qn('#__comment_setting'))
			->where($database->qn('component') . '=' . $database->q($component));

		$database->setQuery($query);
		$row = $database->loadObject();

		if (!$row)
		{
			throw new Exception('No ccomment configuration exist for ' . $component);
		}

		$config = new JRegistry($row->params);
		$config->id = $row->id;
		$config->component = $component;

		// Load the global parameters
		$params = JComponentHelper::getParams('com_comment');
		$config->loadArray($params->toArray());

		// We need arrays of those values
		$config->set('global.censorship_word_list', self::censorWords($config->get('global.censorship_word_list')));
		$config->set('basic.exclude_content_items', self::makeArray($config->get('basic.exclude_content_items')));
		$config->set('basic.disable_additional_comments', self::makeArray($config->get('basic.disable_additional_comments')));

		return $config;
	}

	/**
	 * Create an array out of a string
	 *
	 * @param   string  $string  - the string to explode
	 *
	 * @return array
	 */
	private static function makeArray($string)
	{
		$strings = array();

		if ($string)
		{
			$strings = explode(',', $string);

			foreach ($strings as $key => $value)
			{
				$strings[$key] = trim($value);
			}
		}

		return $strings;
	}

	/**
	 * Transforms the censorship words from a string to an array of words
	 *
	 * @param   string  $censorshipWords  - string with words to censor
	 *
	 * @return array
	 */
	private static function censorWords($censorshipWords)
	{
		$censorshipList = array();

		if ($censorshipWords)
		{
			$censorshipWords = explode(',', $censorshipWords);

			if (is_array($censorshipWords))
			{
				foreach ($censorshipWords as $word)
				{
					$word = trim($word);

					if (StringHelper::strpos($word, '='))
					{
						$word = explode('=', $word);
						$from = trim($word[0]);
						$to = trim($word[1]);
					}
					else
					{
						$from = $word;
						$to = ccommentHelperStrings::str_fill(StringHelper::strlen($word), '*');
					}

					$censorshipList[$from] = $to;
				}
			}
		}

		return $censorshipList;
	}
}
