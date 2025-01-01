<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Site\Controller;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Bluecoder\Component\Jfilters\Site\Model\ResultsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\SiteMenu;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;

/**
 * Class DisplayController
 *
 * @package Bluecoder\Component\Jfilters\Site\Controller
 */
class DisplayController extends BaseController
{

    /**
     * @param                             $config
     * @param   MVCFactoryInterface|null  $factory
     * @param                             $app
     * @param                             $input
     *
     * @throws \Exception
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        $input = Factory::getApplication()->getInput();

        // It's an editor's modal window. We need to fetch it from the admin component
        if ($input->get('view') === 'filters' && $input->get('layout') === 'modal') {
            $config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
        } else {
            $this->setFiltersFromJFMenuItem();
        }

        parent::__construct($config, $factory, $app, $input);
    }

    /**
     * Get the controller's model using the Object Manager
     *
     * @param   string  $name
     * @param   string  $prefix
     * @param   array   $config
     *
     * @return bool|\Joomla\CMS\MVC\Model\BaseDatabaseModel|mixed
     * @throws \Exception
     * @since 1.0.0
     */
    public function getModel($name = '', $prefix = '', $config = [])
    {
        $key = ResultsModel::class;

        // get if from the ObjectManager. Instantiating this model costs, as it runs db queries upon instantiation.
        if (ObjectManager::getInstance()->getContainer()->has($key)) {
            $model = ObjectManager::getInstance()->getContainer()->get($key);
        } else {
            if (empty($name)) {
                $name = $this->getName();
            }

            $model = parent::getModel($name, $prefix, $config);
            ObjectManager::getInstance()->getContainer()->set($key, $model, true);
        }

        return $model;
    }

    /**
     * Set the filters in the \Joomla\Input\Input as they are set in the JF menu item
     *
     * @return void
     * @throws \Exception
     */
    protected function setFiltersFromJFMenuItem(): void
    {
        $selectedFilters = '';
        $input = Factory::getApplication()->getInput();

        try {
            $itemId = $input->getInt('Itemid', 0);
            /** @var SiteMenu $menu */
            $menu = Factory::getApplication()->getMenu();
            $menuItem = $menu->getItem($itemId);
            $selectedFilters = $menuItem ? $menuItem->getParams()->get('selected_filters') : '';
        } catch (\Exception $exception) {
            // suck it. No menu.
        }

        if ($selectedFilters) {
            // dummy url just to achieve a proper parse_url()
            $url = 'https://example.com?' . $selectedFilters;
            $urlComponents = parse_url($url);
            if ($urlComponents && isset($urlComponents['query'])) {
                parse_str($urlComponents['query'], $queryParams);
                foreach ($queryParams as $key => $value) {
                    $existing = $input->get($key, []);
                    $existing = is_scalar($existing) ? [$existing] : $existing;
                    $value = is_scalar($value) ? [$value] : $value;
                    $setValue = array_merge($existing, $value);
                    $input->set($key, $setValue);
                }
            }
        }
    }

    /**
     * Basic method
     *
     * @param   bool   $cachable
     * @param   array  $urlparams
     *
     * @return BaseController
     * @throws \Exception
     * @since 1.0.0
     */
    public function display($cachable = false, $urlparams = [])
    {
        $input = Factory::getApplication()->getInput();

        // Load plugin language files.
        LanguageHelper::loadPluginLanguage();

        // Set the default view name and format from the Request.
        $viewName = $input->get('view', 'results', 'word');
        $input->set('view', $viewName);

        $safeurlparams = array(
            'm'    => 'INT',
            'Itemid' => 'INT',
            'lang' => 'CMD'
        );

        return parent::display($cachable, $safeurlparams);
    }
}
