<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper as JoomlaModuleHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\DatabaseInterface;

/**
 * Extends the abstract JModuleHelper
 * @since     1.6.0
 * @author    Sakis Terz
 */
class ModuleHelper extends JoomlaModuleHelper
{
    /**
     * Get module by id
     *
     * @param   int  $id  The id of the module
     * @param   string   $name
     *
     * @return  \stdClass  The Module object
     * @since   1.5.0
     */
    public static function &getModule($id, $type = 'mod_jfilters_filters', $name = null)
    {
        $result =& self::loadById($id);

        // If we didn't find it, and the name is mod_something, create a dummy object
        if (is_null($result)) {
            $result = parent::getModule($type);
            if (is_null($result)) {
                $result = new \stdClass();
                $result->id = 0;
                $result->title = '';
                $result->module = $type;
                $result->position = '';
                $result->content = '';
                $result->showtitle = 0;
                $result->control = '';
                $result->params = '';
                $result->user = 0;
            }
        }

        return $result;
    }

    /**
     * Load published modules.
     *
     * @param   int $module_id
     * @return  array
     *
     * @since   1.5.0
     */
    protected static function &loadById($module_id)
    {
        static $clean;

        if (isset($clean)) {
            return $clean;
        }
        $app = Factory::getApplication();
        $jinput = $app->input;
        $Itemid = $jinput->get('Itemid', '', 'int');
        $user = Factory::getApplication()->getIdentity();
        $groupsString = implode(',', $user->getAuthorisedViewLevels());
        $lang = Factory::getApplication()->getLanguage()->getTag();
        $clientId = (int)$app->getClientId();

        /** @var CacheControllerFactoryInterface $cache */
        $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController('output', ['defaultgroup' => 'com_modules']);
        $cacheId = md5(serialize(array($Itemid, $groupsString, $clientId, $lang, $module_id)));

        if (!($clean = $cache->get($cacheId))) {
            /** @var DatabaseInterface $db */
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $query->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params, mm.menuid');
            $query->from('#__modules AS m');
            $query->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = m.id');
            $query->where('m.published = 1');

            $query->join('LEFT', '#__extensions AS e ON e.element = m.module AND e.client_id = m.client_id');
            $query->where('e.enabled = 1');

            $query->whereIn('m.access', $user->getAuthorisedViewLevels());
            $query->where('m.client_id = ' . $clientId);
            $query->where('m.id =' . (int)$module_id);
            //for security the module can be only mod_cf_filtering or mod_cf_breadcrumbs type
            $query->where('(m.module ="mod_jfilters_filters" OR m.module="mod_jfilters_selections")');

            // Filter by language
            if ($app->isClient('site') && !empty($lang) && Multilanguage::isEnabled()) {
                $query->whereIn('m.language', [$db->quote($lang), $db->quote('*')]);
            }

            // Set the query
            $db->setQuery($query);
            $module = $db->loadObject();
            $clean = null;

            if (empty($module)) {
                return $clean;
            }

            // Determine if this is a 1.0 style custom module (no mod_ prefix)
            // This should be eliminated when the class is refactored.
            // $module->user is deprecated.
            $file = $module->module;
            $custom = substr($file, 0, 4) == 'mod_' ? 0 : 1;
            $module->user = $custom;
            // 1.0 style custom module name is given by the title field, otherwise strip off "mod_"
            $module->name = $custom ? $module->module : substr($file, 4);
            $module->style = null;
            $module->position = strtolower($module->position);
            $clean = $module;
            $cache->store($clean, $cacheId);
        }

        return $clean;
    }
}