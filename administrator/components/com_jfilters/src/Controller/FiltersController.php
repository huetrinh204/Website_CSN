<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Controller;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as ConfigFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterModel;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;

/**
 * Class FiltersController
 * @package Bluecoder\Component\Jfilters\Administrator\Controller
 */
class FiltersController extends AdminController
{
    /**
     * FiltersController constructor.
     *
     * @param   array                     $config
     * @param   MVCFactoryInterface|null  $factory
     * @param   CMSApplication|null       $app
     * @param   Input|null                $input
     *
     * @throws \Exception
     */
    public function __construct(
        $config = array(),
        MVCFactoryInterface $factory = null,
        ?CMSApplication $app = null,
        ?Input $input = null
    ) {
        parent::__construct($config, $factory, $app, $input);
        $this->registerTask('listen', 'publish');
    }

    /**
     * @param string $name
     * @param string $prefix
     * @param array $config
     * @return bool|\Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since 1.0.0
     */
    public function getModel($name = 'Filter', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Synchronize the filters with the Joomla candidate filters.
     *
     * @throws \Exception
     * @since 1.0.0
     */
    public function synchronize()
    {
        // Check for request forgeries
        $this->checkToken();
        $user = $this->app->getIdentity();
        $message = '';

        if (!$user->authorise('jfilters.synchronize.filters', 'com_jfilters.filters')) {
            $this->app->enqueueMessage(Text::_('COM_JFILTERS_FILTERS_SYNC_NOT_PERMITTED'), 'notice');
        } else {
            $objectManager = ObjectManager::getInstance();
            $logger = $objectManager->getObject(LoggerInterface::class);
            $filtersConfigCollection = $objectManager->getObject(ConfigFilterCollection::class);

            /** @var  FilterModel $resourceModel */
            $resourceModel = $this->getModel('Filter');
            $filtersGenerator = new FilterGenerator($objectManager, $filtersConfigCollection, $resourceModel, $logger);
            $filters = $filtersGenerator->getFilters();
            $savedIds = $resourceModel->saveBatch($filters);
            $resourceModel->deleteBatchOtherThan($savedIds);
            $message = Text::plural('COM_JFILTERS_N_ITEMS_SYNCHRONIZED', count($savedIds));
        }
        $this->setRedirect(Route::_('index.php?option=com_jfilters&view=filters', false), $message);
    }

    /**
     * Purges the filters
     *
     * @return bool
     * @since 1.0.0
     */
    public function purge()
    {
        $user = $this->app->getIdentity();
        if(! $user->authorise('jfilters.generate.filters', 'com_jfilters.filters')) {
            $this->app->enqueueMessage(Text::_('COM_JFILTERS_FILTERS_SYNC_NOT_PERMITTED'), 'notice');
            $return = false;
        }
        else {
            $this->checkToken();
            /** @var  FilterModel $resourceModel */
            $resourceModel = $this->getModel('Filter');
            $return = $resourceModel->purge();

            if (!$return) {
                $message = Text::_('COM_JFILTERS_FILTERS_PURGE_FAILED', $resourceModel->getError());
                $this->setRedirect(Route::_('index.php?option=com_jfilters&view=filters'), $message);

                $return = false;
            } else {
                $message = Text::_('COM_JFILTERS_FILTERS_PURGE_SUCCESS');
                $this->setRedirect(Route::_('index.php?option=com_jfilters&view=filters'), $message);

                $return = true;
            }
        }
        return $return;
    }

    /**
     * Copies filters
     *
     * @throws \Exception
     * @since 1.0.0
     */
    public function copy()
    {
        $this->checkToken();
        $ids    = $this->input->get('cid', [], 'array');
        /** @var  FilterModel $resourceModel */
        $resourceModel = $this->getModel('Filter');
        $newIds = $resourceModel->batchCopy(0, $ids);
        $message = Text::plural('COM_JFILTERS_N_ITEMS_COPIED', count($newIds));
        $this->setRedirect(Route::_('index.php?option=com_jfilters&view=filters', false), $message);
    }
}
