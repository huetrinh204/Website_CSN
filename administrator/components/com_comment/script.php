<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       08.10.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Script file of CComment component
 *
 * @since  5.0
 */
class Com_CommentInstallerScript
{
	public $release = '3.0';

	public $minimum_joomla_release = '2.5.6';

	public $extension = 'com_comment';

	private $type = '';

	private $installationQueue = array(
		'free' => array(
			'plugins' => array(
				'plg_content_joscomment' => 1,
				'plg_k2_ccomment' => 0,
				'plg_search_ccomment' => 0,
				'plg_installer_ccomment' => 1
			)
		),
		'pro' => array(
			'modules' => array(
				// Modules => { (folder) => { (module) => { (position), (published) } }* }*
				'' => array(
					'mod_comments' => array('', 1),
				)
			),
			'plugins' => array(
				'plg_community_compojoomwalls' => 0,
				'plg_compojoomcomment_jomsocial' => 0,
				'plg_content_compojoomcommentjevents' => 0,
				'plg_ninjamonials_compojoomcommentninjamonials' => 0,
				'plg_adsmanagercontent_ccomment' => 0,
				'plg_hwdmediashare_comments_ccomment' => 0,
				'plg_joomgallery_ccomment' => 0,
				'plg_hikashop_ccomment' => 0,
				'plg_content_dpcalendarccomment' => 0,
				'plg_system_ccommentzoo' => 0,
				'plg_system_redshopccomment' => 0,
				'plg_compojoomcomment_aup' => 0,
				'plg_finder_ccomment' => 0,
				'plg_community_ccomment' => 0,
				'plg_djcatalog2_ccomment' => 0,
				'plg_koowa_ccomment' => 0,
				'plg_reditem_ccomment' => 0
			),
			'cbplugins' => array(
				'plug_ccommentwall',
				'plug_usercomments'
			)
		),
		// Key is the name without the lib_ prefix, value if the library should be autopublished
		'libraries' => array(
			'compojoom' => 1
		)
	);

	private $removeOnInstall = array(
		'plugins' => array(
			'plg_content_ccommentzoo' => 0,
			'plg_k2_compojoomcommentk2' => 0,
            'plg_dpcalendar_ccomment' => 0
		)
	);

	/** @var array Obsolete files and folders to remove from the Core release only */
	private $removeFilesCore = array(
		'files' => array(),
		'folders' => array(
			// Oly part of PRO version
			'components/com_comment/classes/akismet',
			'components/com_comment/classes/recaptcha'
		)
	);

	/** @var array Obsolete files and folders to remove from the Core and Pro releases */
	private $removeFilesPro = array(
		'files' => array(
			'administrator/components/com_comment/toolbar.hotspots.html.php',
			'administrator/components/com_comment/toolbar.hotspots.php',
			'administrator/components/com_comment/controllers/about.php',
			'administrator/components/com_comment/controllers/installer.php',
			'administrator/components/com_comment/controllers/joomvertising.php',
			'administrator/components/com_comment/controllers/maintenance.php',
			'administrator/components/com_comment/models/about.php',
			'administrator/components/com_comment/models/installer.php',
			'administrator/components/com_comment/models/joomvertising.php',
			'administrator/components/com_comment/models/maintenance.php',
			'administrator/components/com_comment/library/JOSC_config.php',
			'administrator/components/com_comment/library/JOSC_element.php',
			'administrator/components/com_comment/library/JOSC_library.php',
			'administrator/components/com_comment/library/JOSC_tabRow.php',
			'administrator/components/com_comment/library/JOSC_tabRows.php',
			'administrator/components/com_comment/tables/installer.php',
			'administrator/components/com_comment/install.comment.php',
			'administrator/components/com_comment/uninstall.comment.php',
		),
		'folders' => array(
			'components/com_comment/classes/joomlacomment',
			'components/com_comment/classes/ubbcode',
			'components/com_comment/includes',
			'components/com_comment/joscomment',
			'administrator/components/com_comment/admin_images',
			'administrator/components/com_comment/library/bitfolge',
			'administrator/components/com_comment/library/installer',
			'administrator/components/com_comment/plugin/',
			'administrator/components/com_comment/views/about',
			'administrator/components/com_comment/views/installer',
			'administrator/components/com_comment/views/joomvertising',
			'administrator/components/com_comment/views/maintenance',
			'media/com_comment/rss',
		)
	);

	/**
	 * Executed on install/update/discover
	 *
	 * @param   string                      $type    - the type of th einstallation
	 * @param   JInstallerAdapterComponent  $parent  - the parent JInstaller obeject
	 *
	 * @return boolean - true if everything is OK and we should continue with the installation
	 */
	public function preflight($type, $parent)
	{
        if ($type === "uninstall") {
            return true;
        }

		$path = $parent->getParent()->getPath('source') . '/libraries/compojoom/libraries/compojoom/include.php';

		require_once $path;

		// Load the installer files that come with our package - in case the library is already loaded on the page
		// The library can be loaded if updating using liveupdate, or if any plugin on the page is active
		JLoader::register('CompojoomInstaller', $parent->getParent()->getPath('source') . '/libraries/compojoom/libraries/compojoom/installer/installer.php', true);
		JLoader::register('CompojoomInstallerCb', $parent->getParent()->getPath('source') . '/libraries/compojoom/libraries/compojoom/installer/cb.php', true);
		JLoader::register('CompojoomInstallerAup', $parent->getParent()->getPath('source') . '/libraries/compojoom/libraries/compojoom/installer/aup.php', true);
		JLoader::register('CompojoomDatabaseInstaller', $parent->getParent()->getPath('source') . '/libraries/compojoom/libraries/compojoom/database/installer.php', true);

		$this->installer = new CompojoomInstaller($type, $parent, 'com_comment');

		if (!$this->installer->allowedInstall())
		{
			return false;
		}

		return true;
	}

	/**
	 * method to uninstall the component
	 *
	 * @param   object  $parent  - the parent object
	 *
	 * @return void
	 */
	public function uninstall($parent)
	{
		require_once JPATH_LIBRARIES . '/compojoom/include.php';

		$this->installer = new CompojoomInstaller('uninstall', $parent, 'com_comment');

		$this->status = new stdClass;
		require_once JPATH_ADMINISTRATOR . '/components/com_comment/version.php';

		$params = JComponentHelper::getParams('com_comment');

		// Let us install the modules & plugins
		$plugins = $this->installer->uninstallPlugins($this->installationQueue['free']['plugins']);
		$modules = array();

		if (CCOMMENT_PRO)
		{
			$plugins = array_merge($plugins, $this->installer->uninstallPlugins($this->installationQueue['pro']['plugins']));
			$modules = array_merge($modules, $this->installer->uninstallModules($this->installationQueue['pro']['modules']));
		}

		$this->status->plugins = $plugins;
		$this->status->modules = $modules;

		$this->droppedTables = false;

		if ($params->get('global.complete_uninstall', 0))
		{
			$dbInstaller = new CompojoomDatabaseInstaller(
				array(
					'dbinstaller_directory' => JPATH_ADMINISTRATOR . '/components/com_comment/sql/xml'
				)
			);
			$dbInstaller->removeSchema();
			$this->droppedTables = true;
		}

		echo $this->displayInfoUninstallation();
	}

	/**
	 * method to run after an install/update/discover method
	 *
	 * @param   string  $type    - the type of the installation
	 * @param   object  $parent  - the parent object
	 *
	 * @return void
	 */
	public function postflight($type, $parent)
	{
        if ($type === "uninstall") {
            return true;
        }

		$path = $parent->getParent()->getPath('source');
		require_once $path . '/administrator/components/com_comment/version.php';
		$this->status = new stdClass;

		// Makes sure that the compojoom library tables are created (especially customfields)
		$libraryInstaller = new CompojoomDatabaseInstaller(
			array(
				'dbinstaller_directory' => $path . '/libraries/compojoom/libraries/compojoom/sql/xml'
			)
		);

		$dbInstaller = new CompojoomDatabaseInstaller(
			array(
				'dbinstaller_directory' => $path . '/administrator/components/com_comment/sql/xml'
			)
		);

		$libraryInstaller->updateSchema();
		$dbInstaller->updateSchema();


		// Let us cleanup the old plugins
		$this->installer->uninstallPlugins($this->removeOnInstall['plugins']);

		CommentInstallerDatabase::handleConfig($path);

		if (CCOMMENT_PRO)
		{
			$removeFiles = $this->removeFilesPro;
		}
		else
		{
			$removeFiles = array(
				'files' => array_merge($this->removeFilesPro['files'], $this->removeFilesCore['files']),
				'folders' => array_merge($this->removeFilesPro['folders'], $this->removeFilesCore['folders']),
			);
		}

		$this->installer->removeFilesAndFolders($removeFiles);

		// Don't install the "Installer - Hotspots" plugin for Joomla! 3.0.0+
		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			if (isset($this->installationQueue['free']['plugins']['plg_installer_ccomment']))
			{
				unset($this->installationQueue['free']['plugins']['plg_installer_ccomment']);
			}
		}

		// Let us install the modules & plugins
		$plugins = $this->installer->installPlugins($this->installationQueue['free']['plugins']);
		$modules = array();

		if (CCOMMENT_PRO)
		{
			$plugins = array_merge($plugins, $this->installer->installPlugins($this->installationQueue['pro']['plugins']));
			$modules = array_merge($modules, $this->installer->installModules($this->installationQueue['pro']['modules']));
		}

		$libraries = $this->installer->installLibraries($this->installationQueue['libraries']);

		$this->status->plugins = $plugins;
		$this->status->modules = $modules;
		$this->status->libraries = $libraries;

		// Install the cb plugin if CB is installed
		$this->status->cb = false;

		if (CCOMMENT_PRO)
		{
			foreach ($this->installationQueue['pro']['cbplugins'] as $plugin)
			{
				$this->status->cb = CompojoomInstallerCb::install($parent, $plugin);
			}
		}

        if ($type == 'install') {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__extensions'));
            $defaults = file_get_contents($path . '/administrator/components/com_comment/default_config_params.json');
            $query->set($db->quoteName('params') . ' = ' . $db->quote($defaults));
            $query->where($db->quoteName('name') . ' = ' . $db->quote('com_comment'));
            $db->setQuery($query);
            $db->execute();
        }

        echo $this->displayInfoInstallation();

		if (strstr(Juri::getInstance()->toString(), 'view=liveupdate&task=install'))
		{
			JFactory::getApplication()->enqueueMessage($this->displayInfoInstallation());
			JFactory::getApplication()->redirect('index.php?option=com_comment');
		}
	}

	/**
	 * Displays information about the status of the installation
	 *
	 * @return string
	 */
	private function displayInfoInstallation()
	{
		$html[] = $this->addCSS();
		$html[] = '<div class="ccomment-info alert alert-info">'
			. JText::sprintf('COM_COMMENT_INSTALLATION_SUCCESS', (CCOMMENT_PRO ? 'Professional' : 'Core')) . '</div>';

		if (!CCOMMENT_PRO)
		{
			$html[] .= '<p>' . JText::sprintf('COM_COMMENT_UPGRADE_TO_PRO', 'https://compojoom.com/joomla-extensions/compojoomcomment') . '</p>';
		}

		$html[] = CompojoomHtmlTemplates::renderSocialMediaInfo();

		if ($this->status->libraries)
		{
			$html[] = $this->installer->renderLibraryInfoInstall($this->status->libraries);
		}

		if ($this->status->cb)
		{
			$html[] = '<br /><span style="color:green;">Community builder detected. CB plugin installed!</span>';
		}

		if ($this->status->plugins)
		{
			$html[] = $this->installer->renderPluginInfoInstall($this->status->plugins);
		}

		if ($this->status->modules)
		{
			$html[] = $this->installer->renderModuleInfoInstall($this->status->modules);
		}

		return implode('', $html);
	}

	/**
	 * Ads css to the page
	 *
	 * @return string
	 */
	public function addCss()
	{
		$css = '<style type="text/css">
					.ccomment-info {
						background-color: #D9EDF7;
					    border-color: #BCE8F1;
					    color: #3A87AD;
					    border-radius: 4px 4px 4px 4px;
					    padding: 8px 35px 8px 14px;
					    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
					    margin-bottom: 18px;
					}

				</style>
				';

		return $css;
	}

	/**
	 * Displays uninstall info to the user
	 *
	 * @return string
	 */
	public function displayInfoUninstallation()
	{
		$html[] = $this->addCss();
		$html[] = '<div class="ccomment-info alert alert-info">CComment is now removed from your system</div>';

		if ($this->droppedTables)
		{
			$html[] = '<p>The option uninstall complete mode was set to true. Database tables were removed</p>';
		}
		else
		{
			$html[] = '<p>The option uninstall complete mode was set to false. The database tables were not removed.</p>';
		}

		$html[] = $this->installer->renderPluginInfoUninstall($this->status->plugins);
		$html[] = $this->installer->renderModuleInfoUninstall($this->status->modules);

		$html[] = CompojoomHtmlTemplates::renderSocialMediaInfo();

		return implode('', $html);
	}
}

/**
 * Class CommentInstallerDatabase
 *
 * @since  5.0
 */
class CommentInstallerDatabase
{
	/**
	 * Decide whether it should update the configs, or create the default one
	 *
	 * @param   string  $path  - path to the config
	 *
	 * @return void
	 */
	public static function handleConfig($path)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*) as count')->from('#__comment_setting');
		$db->setQuery($query);
		$count = $db->loadObject();

		if (isset($count->count) && $count->count)
		{
			self::updateConfigs($path);
		}
		else
		{
			self::insertConfig($path);
		}
	}

	/**
	 * Update old configs that don't use json to store the settings
	 *
	 * @param   string  $path  - path to the config
	 *
	 * @return array
	 */
	public static function updateConfigs($path)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$json = self::getDefaultConfig($path);
		$updates = array();

		$query->select('*')->from('#__comment_setting');
		$db->setQuery($query);
		$configs = $db->loadObjectList();

		foreach ($configs as $config)
		{
			// Convert the params to JSON
			$params = json_decode($config->params, true);

			// If we have a valid json config, we don't have to do anything
			if ($params === null)
			{
				$query->clear();
				$query->update('#__comment_setting')->set('params = ' . $db->q($json));
				$db->setQuery($query);
				$updates[$config->component] = $db->execute();
			}
		}

		return $updates;
	}

	/**
	 * Create a new default config
	 *
	 * @param   string  $path  - path to the config
	 *
	 * @return mixed
	 */
	public static function insertConfig($path)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$json = self::getDefaultConfig($path);

		$query->insert('#__comment_setting')->columns('note,component,params')
			->values($db->q('The standard joomla article manager') . ',' . $db->q('com_content') . ',' . $db->q($json));
		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Get the default config
	 *
	 * @param   string  $path  - path to the config
	 *
	 * @return mixed|string
	 */
	public static function getDefaultConfig($path)
	{
		$settings = $path . '/administrator/components/com_comment/models/forms/settings.xml';
		$template = $path . '/components/com_comment/templates/default/settings.xml';
		$form = new JForm('comment');
		$form->loadFile($settings);
		$form->loadFile($template);

		$json = array();

		$fieldsets = $form->getFieldsets();

		foreach ($fieldsets as $fieldsetkey => $fieldset)
		{
			$fields = $form->getFieldset($fieldsetkey);

			foreach ($fields as $fieldkey => $field)
			{
				$json[$field->group][$field->fieldname] = $field->value;
			}
		}

		return json_encode($json);
	}
}
