<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseDriver;

/**
 * Load the installer
 */
class Pkg_jfiltersInstallerScript
{
    /**
     * The name of our package, e.g. pkg_example. Used for dependency tracking.
     *
     * @var  string
     */
    protected string $packageName = 'pkg_jfilters.xml';

    /**
     * The name of our component, e.g. com_example. Used for dependency tracking.
     *
     * @var  string
     */
    protected string $componentName = 'com_jfilters';

    /**
     * The minimum PHP version required to install this extension
     *
     * @var   string
     */
    protected string $minimumPHPVersion = '7.4.0';

    /**
     * The minimum Joomla! version required to install this extension
     *
     * @var   string
     */
    protected string $minimumJoomlaVersion = '4.0.0';

    /**
     * The maximum Joomla! version this extension can be installed on
     *
     * @var   string
     */
    protected $maximumJoomlaVersion = '5.999.999';

    /**
     * Cache the extension objects from the database.
     *
     * @var arrays
     */
    protected $databaseExtension;

    /**
     * @var bool
     */
    protected $isPro;

    /**
     * These files will be removed in the FREE version.
     *
     * @var string[]
     */
    protected array $proFiles = [
        'modules/mod_jfilters_filters/tmpl/_buttons_multi.php',
        'modules/mod_jfilters_filters/tmpl/_buttons_single.php',
        'modules/mod_jfilters_filters/tmpl/_calendar.php',
        'modules/mod_jfilters_filters/tmpl/_list.php',
        'modules/mod_jfilters_filters/tmpl/_list_search.php',
        'modules/mod_jfilters_filters/tmpl/_radios.php',
        'modules/mod_jfilters_filters/tmpl/_range_inputs.php',
        'modules/mod_jfilters_filters/tmpl/_range_sliders.php',
        'modules/mod_jfilters_filters/tmpl/_range_inputs_sliders.php',
    ];

    /**
     * The component's config default settings.
     *
     * @var array
     */
    protected array $componentDefaultSettings = [
        "toggle_state" => "expanded",
        "options_sort_by" => "label",
        "options_sort_direction" => "asc",
        // We can set different default value for FREE and PRO
        "show_clear_option" => "1",
        "show_option_counter" => ['FREE' => "0", 'PRO' => "1"],
        "list_search" => "0",
        "max_option_label_length" => 55,
        "max_option_value_length" => 35,
        //Tree
        "nested_toggle_state" => "collapsed",
        "parent_node_linkable" => "1",
        "show_sub_node_contents_on_parent" => "0",
        //Seo
        "max_path_nesting_levels" => 2,
        "show_in_page_title" => "1",
        "follow_links" => "0",
        "use_canonical" => "1",
        // Results
        "show_taxonomy" => "1",
        "show_description" => "1",
        "description_length" => 255,
        "show_image" => "1",
        "link_image" => "1",
        "show_date" => "1",
        "show_url" => "0",
        //Advanced
        "profiling" => "0",
        "edit_filters_config_file_path" => "0",
        "filters_config_file_path" => "administrator/components/com_jfilters/config_presets/filters.xml",
        "edit_contexts_config_file_path" => "0",
        "contexts_config_file_path" => "administrator/components/com_jfilters/config_presets/contexts.xml",
        "edit_dynamic_filters_config_file_path" => "0",
        "dynamic_filters_config_file_path" => "administrator/components/com_jfilters/config_presets/filters/dynamic.xml",
        "edit_preferences_file_path" => "0",
        "preferences_file_path" => "administrator/components/com_jfilters/config_presets/preferences.php"
    ];

    /**
     * The messages that going to print.
     *
     * @var array
     */
    protected $printed_messages = [];

    /**
     * A list of extensions (modules, plugins) to enable after installation. Each item has four values, in this order:
     * type (plugin, module, ...), name (of the extension), client (0=site, 1=admin), group (for plugins), edition (pro, free).
     *
     * @var array
     */
    protected array $extensionsToEnableOnInstall = [
        ['plugin', 'jfilters', 0, 'content', 'free'],
        ['plugin', 'jfiltersindexer', 0, 'finder', 'free'],
        ['plugin', 'fieldsubform', 0, 'jfilters', 'free'],
        ['plugin', 'jfiltersfilters', 0, 'editors-xtd', 'free'],
        ['plugin', 'jfiltersajax', 0, 'system' , 'pro']
    ];

    protected array $extensionsToEnableOnUpgrade = [
        ['plugin', 'jfiltersajax', 0, 'system' , 'pro']
    ];

    /**
     * Add messages to be displayed after installation, e.g. new features
     *
     * @var array
     */
    protected array $messages = [];

    /**
     * Preflight routine executed before install and update
     *
     * @param        $type    string    type of change (install, update or discover_install)
     * @since        1.0.0
     */
    public function preflight($type, $parent)
    {

        // Check the minimum PHP version
        if (!version_compare(PHP_VERSION, $this->minimumPHPVersion, 'ge')) {
            $msg = "<p>You need PHP $this->minimumPHPVersion or later to install this package</p>";
            Log::add($msg, Log::WARNING, 'jerror');

            return false;
        }

        // Check the minimum Joomla! version
        if (!version_compare(JVERSION, $this->minimumJoomlaVersion, 'ge')) {
            $msg = "<p>You need Joomla! $this->minimumJoomlaVersion or later to install this component</p>";
            Log::add($msg, Log::WARNING, 'jerror');

            return false;
        }

        // Check the maximum Joomla! version
        if (!version_compare(JVERSION, $this->maximumJoomlaVersion, 'le')) {
            $msg = "<p>You need Joomla! $this->maximumJoomlaVersion or earlier to install this component</p>";
            Log::add($msg, Log::WARNING, 'jerror');

            return false;
        }

        if ($type == 'update') {
            $milestone_versions = array_keys($this->messages);
            $this->printed_messages = [];
            $oldRelease = $this->getParam('version');
            foreach ($milestone_versions as $m_v) {
                if (version_compare($oldRelease, $m_v) == -1) {
                    $this->printed_messages[] = $this->messages[$m_v];
                }
            }
        }
    }

    /**
     * Get the files included in the package's xml under the files node
     *
     * @param Xml $manifest
     * @return array
     */
    private function getPackageExtensions($manifest)
    {
        $includedFiles = [];
        $files = $manifest->xpath('files/file');
        foreach ($files as $file) {
            $type = (string)$file->attributes()->type;
            $name = (string)$file->attributes()->id;
            $description = (string)$file->attributes()->description;
            $includedFiles[] = ['name' => $name, 'type' => $type, 'description' => $description];
        }

        return $includedFiles;
    }

    /**
     * Get a variable from the manifest file (actually, from the manifest cache).
     *
     * @param string $name
     * @return string
     * @since 1.0.0
     */
    private function getParam($name)
    {
        $param = '';
        $extension = $this->getInstalledExtension();
        if($extension) {
            $manifest = new Joomla\Registry\Registry($extension->manifest_cache);
            $param = $manifest->get($name);
        }
        return $param;
    }

    /**
     * Get an extension from the db
     *
     * @param   string  $extensionName
     *
     * @return arrays
     * @since 1.0.0
     */
    protected function getInstalledExtension($extensionName = "com_jfilters")
    {
        if(!isset($this->databaseExtension[$extensionName])) {
            /** @var DatabaseDriver $db */
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->select([$db->quoteName('client_id'), $db->quoteName('manifest_cache'), $db->quoteName('params')])
                  ->from($db->quoteName('#__extensions'))
                  ->where($db->quoteName('element') . '=' . $db->quote($extensionName));
            $db->setQuery($query);
            $this->databaseExtension[$extensionName] = $db->loadObject();
        }
        return $this->databaseExtension[$extensionName];
    }

    /**
     * Postflight routine executed after install and update
     *
     * @param        $type    string    type of change (install, update or discover_install)
     *
     * @since        1.0.0
     */
    public function postflight($type, $parent)
    {
        if ($type == 'install') {
            $this->enableExtensions();
        } elseif($type == 'update') {
            $this->enableExtensions('update');
        }

        if ($type == 'install' || $type == 'update') {
            $this->setComponentDefaultParams();
            $this->showMessage($type);
        }

        if (($type == 'install' || $type == 'update') && !$this->isPro()) {
            // Remove no pro files
            foreach ($this->proFiles as $file) {
                $fileName = JPATH_ROOT . '/' . $file;
                if (file_exists($fileName)) {
                    unlink($fileName);
                }
            }
        }
    }

    /**
     * Enable modules and plugins after installing them
     */
    private function enableExtensions($type = 'install')
    {
        if ($type == 'install') {
            $extensionsToActivate = $this->extensionsToEnableOnInstall;
        } else {
            $extensionsToActivate = $this->extensionsToEnableOnUpgrade;
        }

        foreach ($extensionsToActivate as $extension)
        {
            list($type, $name, $client, $group, $edition) = $extension;
            $this->enableExtension($type, $name, $client, $group, $edition);
        }
    }

    /**
     * Set default params for the component
     *
     * @since 1.0.0
     */
    private function setComponentDefaultParams()
    {
        if ($this->componentDefaultSettings) {

            $component = $this->getInstalledExtension($this->componentName);
            if ($component) {
                // If there are component params set, make sure that all have a set value
                if ($component->params && $component->params != '{}') {
                    $params = new Joomla\Registry\Registry($component->params);
                    $edition = $this->isPro() ? 'PRO' : 'FREE';
                    foreach ($this->componentDefaultSettings as $settingName => $settingValue) {
                        $defaultSettingValue = is_array($settingValue) && !empty($settingValue[$edition]) ? $settingValue[$edition] : (!is_array($settingValue) ? $settingValue : '');
                        $imposedSettingValue = false;
                        /*
                         * In case of update/downgrade, maybe exist a setting value of the PRO edition to the FREE.
                         * We have to impose the new setting value. Otherwise a param field maybe locked/disabled with the wrong value.
                         */
                        if ($edition == 'FREE' && is_array($settingValue) && $params->get($settingName) == $settingValue['PRO']) {
                            $imposedSettingValue = $settingValue['FREE'];
                        }

                        $setParamValue = $imposedSettingValue !== false ? $imposedSettingValue : $params->get($settingName, $defaultSettingValue);
                        $params->set($settingName, $setParamValue);
                    }
                } else {
                    // No params, set the default. Normalize the config to get the defaults by edition (PRO/FREE)
                    $normalizedComponentConfig = $this->getNormalizeComponentConfigByEdition();
                    $params = new Joomla\Registry\Registry($normalizedComponentConfig);
                }
            }

            // Put the params in the db
            try
            {
                /** @var DatabaseDriver $db */
                $db = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true)
                            ->update('#__extensions')
                            ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
                            ->where('element = ' . $db->quote($this->componentName));
                $db->setQuery($query);
                $db->execute();
            }
            catch (\Exception $e)
            {
                // Suck it. It works without params.
            }
        }
    }

    /**
     * Get the proper component settings by edition
     *
     * @return array
     * @since 1.9.0
     */
    private function getNormalizeComponentConfigByEdition()
    {
        $isPro = $this->isPro();
        $normalizedComponentConfig = [];

        foreach ($this->componentDefaultSettings as $settingName => $settingValue) {
            // We have different value per edition
            if (is_array($settingValue) && isset($settingValue['FREE']) && isset($settingValue['PRO'])) {
                if ($isPro) {
                    $settingValue = $settingValue['PRO'];
                } else {
                    $settingValue = $settingValue['FREE'];
                }
            }
            $normalizedComponentConfig[$settingName] = $settingValue;
        }

        return $normalizedComponentConfig;
    }

    /**
     * Enable an extension
     *
     * @param   string   $type    The extension type.
     * @param   string   $name    The name of the extension (the element field).
     * @param   int      $client  The application id (0: Joomla CMS site; 1: Joomla CMS administrator).
     * @param   string   $group   The extension group (for plugins).
     * @param   string   $edition The extension edition ('pro', 'free').
     * @return  bool
     */
    private function enableExtension($type, $name, $client = 1, $group = null, $edition = 'free')
    {
        $extensionEditionIsPro = $this->isPro();

        // Do not enable PRO extensions. Possibly do not exist in the package.
        if (!$extensionEditionIsPro && $edition == 'pro') {
            return false;
        }

        try
        {
            /** @var DatabaseDriver $db */
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                        ->update('#__extensions')
                        ->set($db->quoteName('enabled') . ' = ' . $db->quote(1))
                        ->where('type = ' . $db->quote($type))
                        ->where('element = ' . $db->quote($name));
        }
        catch (\Exception $e)
        {
            // Suck it
            return false;
        }


        switch ($type)
        {
            case 'plugin':
                // Plugins have a folder but not a client
                $query->where('folder = ' . $db->quote($group));
                break;

            case 'language':
            case 'module':

            case 'library':
            case 'package':
            case 'component':
            default:
                // Components, packages and libraries don't have a folder or client.
                // Included for completeness.
                break;
        }

        try
        {
            $db->setQuery($query);
            $db->execute();
        }
        catch (\Exception $e)
        {
            // Suck it
        }
        return true;
    }

    /*
     * @return bool
     */
    private function isPro() : bool 
    {
        if ($this->isPro === null) {
            $extensionVars = @include JPATH_ADMINISTRATOR . '/components/com_jfilters/env.php';
            $extensionEditionIsPro = is_array($extensionVars) && isset($extensionVars['edition']) && $extensionVars['edition'] == 'PRO' ? true : false;
            $this->isPro = $extensionEditionIsPro;
        }

        return $this->isPro;
    }

    /**
     * Displays post installation messages
     *
     * @param $type
     */
    private function showMessage($type)
    {
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_jfilters', JPATH_ADMINISTRATOR);
        $language->load('com_config', JPATH_ADMINISTRATOR);

        ?>
        <div class="card p-3">
            <h2 class="card-subtitle" style="font-size: 1.6rem; font-weight:normal; color:#737373; ">
                <img style="margin-inline-end: 0.5rem; height: 1.3rem; vertical-align: baseline;" src="../media/com_jfilters/images/jfilters_logo.svg" alt="jfilters logo"/>JFilters
            </h2>
            <p class="card-title mt-4 mb-4"><?= Text::_('COM_JFILTERS_XML_DESCRIPTION')?></p>
            <p class="actions">
                <a class="btn btn-primary" href="index.php?option=com_jfilters" style="color: var(--btn-color, #fff);">
                    <?= Text::_('JOPEN')?> JFilters <?= Text::_('COM_CONFIG_HEADING_COMPONENT')?></a>

                <?php if($type == 'install') {
                    // Getting started guide link, in case of install.
                    ?>
                <a class="btn btn-light border-dark mx-3" href="https://docs.blue-coder.com/jfilters/getting-started/installation" target="_blank" rel="noopener">
                    <?= Text::_('COM_JFILTERS_GETTING_STARTED_GUIDE')?> <span class="text-secondary"><span class="icon-clock"></span> 5' read</span></a>
                <?php }
                elseif($type == 'update') {
                    // Changelog link, in case of update.
                    ?>
                    <a class="btn btn-light border-dark mx-3" href="https://blue-coder.com/changelogs/jfilters?utm_source=joomla&utm_medium=installation&utm_campaign=changelog" target="_blank" rel="noopener">
                        Changelog</a>
                <?php } ?>
            </p>
            <div class="position-absolute d-none d-xl-block" style="bottom:16%; margin-inline-start: 80%;">
                <div style="opacity: 0.5;" class="mb-2">Developed By</div>
                <img src="../media/com_jfilters/images/bluecoder_logo_full.svg" alt="Bluecoder logo" style="height: 32px">
            </div>
        </div>
        <?php
        //if update messages
        if (!empty($this->printed_messages)) {
            $language->load('com_messages', JPATH_ADMINISTRATOR);
            ?>
            <div class="clr clearfix"></div>

            <h3><?php echo Text::_('COM_MESSAGES_READ'); ?></h3>
            <div id="system-message-container">
                <?php
                foreach ($this->printed_messages as $message) {?>
                    <div class="alert alert-info"><?php echo $message ?></div>
                <?php } ?>
            </div>
        <?php }
    }
}