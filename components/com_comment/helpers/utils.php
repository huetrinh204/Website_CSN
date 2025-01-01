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

// Load the compojoom framework
require_once JPATH_LIBRARIES . '/compojoom/include.php';

JLoader::discover('ccomment', JPATH_SITE . '/administrator/components/com_comment/library');
JLoader::discover('ccommentComponent', JPATH_SITE . '/administrator/components/com_comment/library/component');
JLoader::discover('ccommentModel', JPATH_SITE . '/components/com_comment/models');
JLoader::discover('ccommentHelper', JPATH_SITE . '/components/com_comment/helpers/');

/**
 * Class ccommentHelperUtils
 *
 * @since  5
 */
class CcommentHelperUtils
{
	private static $icons = array();

	/**
	 * This function loads the Settings class for a comment plugin
	 *
	 * @param   string  $component  - the component that we need the options for
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public static function getComponentSettings($component)
	{
		$nameParts = explode('_', $component);
		$path      = JPATH_SITE . '/administrator/components/com_comment/plugins/' . $component . '/settings.php';
		$class     = 'ccommentComponent' . ucfirst($nameParts[1]) . 'Settings';
		JLoader::register($class, $path);

		if (!JLoader::load($class))
		{
			throw new Exception('Options file for ' . $component . 'doesn\'t exist');
		}

		return new $class;
	}


	/**
	 * This function initialises the comment plugin object
	 *
	 * @param   string  $component  - the component name (com_something)
	 * @param   object  $row        - the object that we are going to comment on
	 * @param   object  $params     - any parameters
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public static function getPlugin($component, $row = null, $params = null)
	{
		$nameParts = explode('_', $component);
		$path      = JPATH_SITE . '/administrator/components/com_comment/plugins/' . $component . '/' . $nameParts[1] . '.php';
		$class     = 'ccommentComponent' . ucfirst($nameParts[1]) . 'Plugin';
		JLoader::register($class, $path);

		if (!JLoader::load($class))
		{
			throw new Exception('CComment plugin file for ' . $component . 'doesn\'t exist');
		}

		return new $class($row, $params);
	}

	/**
	 * Initialises the comment system
	 *
	 * @param   string  $component  - the component name
	 * @param   object  $row        - the object that we are going to comment on
	 * @param   object  $params     - any component parameters
	 *
	 * @return string
	 */
	public static function commentInit($component, $row, $params = null)
	{
		$input = JFactory::getApplication()->input;
		$input->set('component', $component);
		$plugin = self::getPlugin($component, $row, $params);

		if (!$plugin->isEnabled())
		{
			return '';
		}

		self::loadLanguage();

		if ($plugin->isSingleView())
		{
			// Process mailqueue if we are in single view
			if (JComponentHelper::getParams('com_comment')->get('global.mailqueue_pageload', 1))
			{
				ccommentHelperQueue::send();
			}

			return self::loadSingleView($plugin, $component);
		}
		else
		{
			if ($plugin->showReadOn())
			{
				return self::loadListView($plugin, $component);
			}
		}

		return '';
	}

	/**
	 * Loads the necessary language files for the component
	 *
	 * @return void
	 */
	public static function loadLanguage()
	{
		CompojoomLanguage::load('com_comment', JPATH_ADMINISTRATOR);
		CompojoomLanguage::load('com_comment.sys', JPATH_ADMINISTRATOR);
		CompojoomLanguage::load('com_comment', JPATH_SITE);
	}

	/**
	 * Gets just the comment count for the article. Some components need just the
	 * int value for count and not the whole write comment (x comments) that we normally
	 * generate
	 *
	 * Example: look at our easyblog integration
	 *
	 * @param   object  $plugin     - the comment plugin object
	 * @param   string  $component  - the component name
	 *
	 * @return string
	 */
	public static function getCommentCount($plugin, $component)
	{
		$model = JModelLegacy::getInstance('Comment', 'ccommentModel');
		JFactory::getApplication()->input->set('component', $component);

		return $model->countComments($plugin->getPageId(), $component);
	}

	/**
	 * Loads the comment system on a list view
	 *
	 * @param   object  $plugin     - the comment plugin object
	 * @param   string  $component  - the component name
	 *
	 * @return string
	 */
	public static function loadListView($plugin, $component)
	{
		$config = ccommentConfig::getConfig($component);
		JLoader::register('ccommentViewComments', JPATH_SITE . '/components/com_comment/views/comments/view.html.php');
		$model = JModelLegacy::getInstance('Comment', 'ccommentModel');
		$id    = $plugin->getPageId();
		$count = $model->countComments($id, $component);

		$view = new ccommentViewComments(
			array('base_path' => JPATH_SITE . '/components/com_comment')
		);

		$view->config = $config;
		$view->count  = $count;
		$view->plugin = $plugin;
		$view->link   = self::fixUrl($plugin->getLink($plugin->getPageId()));

		if ($config->get('template_params.preview_visible', 0))
		{
			$comments       = $model->getPreviewComments($id, $component);
			$view->comments = ccommentHelperComment::prepareCommentForPreview($plugin, $comments);
		}

		$view->setLayout('readmore');
		$html = $view->readMore();

		return $html;
	}

	/**
	 * Loads the single view for the comments
	 *
	 * @param   object  $plugin     - the comment plugin object
	 * @param   string  $component  - the component name
	 *
	 * @return mixed|string|void
	 */
	public static function loadSingleView($plugin, $component)
	{
		JLoader::register('ccommentViewComments', JPATH_SITE . '/components/com_comment/views/comments/view.html.php');

		$config = ccommentConfig::getConfig($component);
		$model  = JModelLegacy::getInstance('Comment', 'ccommentModel');
		$id     = $plugin->getPageId();
		$count  = $model->countComments($id, $component);
		$view   = new ccommentViewComments(
			array('base_path' => JPATH_SITE . '/components/com_comment')
		);

		$view->setLayout('default');
		$view->plugin    = $plugin;
		$view->config    = $config;
		$view->count     = $count;
		$view->contentId = $id;
		$view->component = $component;

		$html = $view->display();

		return $html;
	}

	/**
	 * Sends the json response by properly setting a header
	 *
	 * @param   object|array  $data  - the data that is going to be sent to the client
	 *
	 * @return void
	 */
	public static function sendJsonResponse($data)
	{
		header('content-type:application/json');
		echo json_encode($data);
	}

	/**
	 * Returns options that are going to be used by the javascript on the client side
	 *
	 * @param   string  $component  - the component name
	 *
	 * @return array
	 *
	 * @since  4.0
	 */
	public static function getJSConfig($component)
	{
		$config    = ccommentConfig::getConfig($component);
		$uri       = JUri::getInstance();
		$user      = JFactory::getUser();
		$lang      = JFactory::getLanguage();
		$languages = JLanguageHelper::getLanguages('lang_code');

		$languageCode = $languages[$lang->getTag()]->sef;

		$jsconfig = array(
			'comments_per_page'   => (int) $config->get('layout.comments_per_page'),
			'sort'                => (int) $config->get('layout.sort'),
			'tree'                => (int) $config->get('layout.tree'),
			'use_name'            => (int) $config->get('layout.use_name'),
			'tree_depth'          => (int) $config->get('layout.tree_depth', 5),
			'form_position'       => (int) $config->get('template_params.form_position'),
			'voting'              => (int) $config->get('layout.voting_visible'),
			'copyright'           => (int) $config->get('layout.show_copyright', 1),
			'pagination_position' => (int) $config->get('template_params.pagination_position'),
			'avatars'             => (int) $config->get('integrations.support_avatars'),
			'gravatar'            => (int) $config->get('integrations.gravatar'),
			'support_ubb'         => (int) $config->get('layout.support_ubb'),
			'support_emoticons'   => (int) $config->get('layout.support_emoticons'),
			'support_picture'     => (int) $config->get('layout.support_pictures'),
			'name_required'       => (int) $config->get('template_params.required_user'),
			'email_required'      => (int) $config->get('template_params.required_email'),
			'baseUrl'             => $uri->base(),
			'langCode'            => $languageCode
		);

		// Create the emoticons for the SCEditor if we are supporting them
		if ($config->get('layout.support_emoticons'))
		{
			$i                       = 0;
			$jsonEmoticons['hidden'] = array();
			$emoticons               = ccommentHelperUtils::getEmoticons($config);

			foreach ($emoticons as $key => $emoticon)
			{
				if ($i < 10)
				{
					$jsonEmoticons['dropdown'][$key] = $emoticon;
				}
				else
				{
					$jsonEmoticons['more'][$key] = $emoticon;
				}

				$i++;
			}

			$jsconfig['emoticons_pack'] = $jsonEmoticons;
		}

		$jsconfig['file_upload'] = self::getFileUploadConfig();

		if ($config->get('security.captcha') && $config->get('security.captcha_type') == 'recaptcha'
			&& ccommentHelperSecurity::groupHasAccess($user->getAuthorisedGroups(), (array) $config->get('security.captcha_usertypes')))
		{
			$jsconfig['captcha_pub_key'] = $config->get('security.recaptcha_public_key');
		}

		return $jsconfig;
	}

	/**
	 * The settings for the fileupload widget
	 *
	 * @return array
	 *
	 * @since version
	 */
	public static function getFileUploadConfig()
	{
		$params    = JComponentHelper::getParams('com_comment');
		$imageSize = explode('x', $params->get('thumbs.original', '2400x1800'));

		$displayData = array(
			'url'              => JUri::getInstance()->base() . 'index.php?option=com_comment&amp;task=multimedia.doIt',
			'formControl'      => 'jform',
			'fieldName'        => 'picture',
			'maxNumberOfFiles' => $params->get('max_number_of_files', 3),
			'fileTypes'        => $params->get('image_extensions'),
			'maxSize'          => $params->get('upload_maxsize'),
			'component'        => 'com_comment',
			'imageSize'        => array('x' => $imageSize[0], 'y' => $imageSize[1])
		);

		return $displayData;
	}

	/**
	 * Outputs language strings in the appropriate language to be used with Javascript
	 *
	 * @return void
	 */
	public static function getJsLocalization()
	{
		$strings = array(
			'COM_COMMENT_PLEASE_FILL_IN_ALL_REQUIRED_FIELDS',
			'COM_COMMENT_ANONYMOUS'
		);

		foreach ($strings as $string)
		{
			JText::script($string);
		}
	}

	/**
	 * Censors a given string
	 *
	 * @param   string     $text    - the text to censor
	 * @param   JRegistry  $config  - the configuration object for the component
	 *
	 * @return mixed
	 */
	public static function censorText($text, $config)
	{
		if ($config->get('global.censorship'))
		{
			$words = $config->get('global.censorship_word_list');

			if (count($words))
			{
				$replace = 'str_ireplace';

				if ($config->get('global.censorship_case_sensitive', 1))
				{
					$replace = 'str_replace';
				}

				foreach ($words as $from => $to)
				{
					$text = call_user_func($replace, $from, $to, $text);
				}
			}
		}

		return $text;
	}


	/**
	 * Gets the itemid for a component
	 *
	 * @param   string  $component  - the component that we look for (com_something)
	 *
	 * @return int - component ID
	 */
	public static function getItemid($component = '')
	{
		static $ids;

		if (!isset($ids))
		{
			$ids = array();
		}

		if (!isset($ids[$component]))
		{
			$database = JFactory::getDBO();
			$query    = "SELECT id FROM #__menu"
				. "\n WHERE link LIKE '%option=$component%'"
				. "\n AND type = 'component'"
				. "\n AND published = 1 LIMIT 1";
			$database->setQuery($query);
			$ids[$component] = $database->loadResult();
		}

		return $ids[$component];
	}

	/**
	 * Gets the emoticons
	 *
	 * @param   object  $config  - a comment plugin config object
	 *
	 * @return array
	 */
	public static function getEmoticons($config)
	{
		$appl         = JFactory::getApplication();
		$icons        = array();
		$override     = false;
		$pack         = $config->get('layout.emoticon_pack');
		$path         = JPATH_SITE . '/components/com_comment/assets/emoticons/' . $pack . '/config.php';
		$pathOverride = JPATH_SITE . '/templates/' . $appl->getTemplate() . '/html/com_comment/emoticons/' . $pack . '/config.php';

		if (is_file($pathOverride))
		{
			$override = true;
			$path     = $pathOverride;
		}

		if (is_file($path) && !isset(self::$icons[$pack]))
		{
			require_once $path;

			if (isset($ccommentEmoticons))
			{
				if ($override)
				{
					$src = JUri::root(true) . '/templates/' . $appl->getTemplate() . '/html/com_comment/emoticons/' . $pack . '/images/';
				}
				else
				{
					$src = JUri::root(true) . '/media/com_comment/emoticons/' . $pack . '/images/';
				}

				foreach ($ccommentEmoticons as $key => $value)
				{
					self::$icons[$pack][$key] = $src . $value;
				}
			}
		}

		return self::$icons[$pack];
	}

	/**
	 * Makes an url with scheme, host and port - if necessary
	 *
	 * @param   string  $url  - the url to fix
	 *
	 * @return string
	 */
	public static function fixUrl($url)
	{
		if (substr(ltrim($url), 0, 7) != 'http://' && substr(ltrim($url), 0, 8) != 'https://')
		{
			$uri  = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'host', 'port'));
			$url  = $base . $url;
		}

		return $url;
	}
}
