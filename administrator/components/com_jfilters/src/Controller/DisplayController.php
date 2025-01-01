<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Controller;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Helper\LayoutHelper;
use Bluecoder\Component\Jfilters\Administrator\Helper\PluginHelper as JfPluginHelper;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ConfigContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\ContextInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Filter\Collection as ConfigFilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as filterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterGenerator;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterModel;
use Bluecoder\Component\Jfilters\Administrator\Model\LoggerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

/**
 * Fields Controller
 *
 * @since  1.0.0
 */
class DisplayController extends BaseController
{
    /**
     * @var string
     * @since 1.0.0
     */
    protected $default_view = 'filters';

    /**
     * @param bool $cachable
     * @param bool $urlparams
     * @return BaseController
     * @throws \ReflectionException
     * @since 1.0.0
     */
    public function display($cachable = false, $urlparams = false)
    {
        $view = $this->input->get('view', 'filters');
        $this->systemCheck();

        //generate filters only in the filters view
        if ($view == 'filters') {
            $objectManager = ObjectManager::getInstance();
            $logger = $objectManager->getObject(LoggerInterface::class);

            /** @var filterCollection $filtersCollection */
            $filtersCollection = $objectManager->getObject(filterCollection::class);

            //auto-generate filters only if nothing is found.
            if ($filtersCollection->getSize() == 0) {
                $filtersConfigCollection = $objectManager->getObject(ConfigFilterCollection::class);
                /** @var  FilterModel $resourceModel */
                $resourceModel = $this->getModel('Filter');
                $filtersGenerator = new FilterGenerator($objectManager, $filtersConfigCollection, $resourceModel,
                    $logger);
                $filters = $filtersGenerator->getFilters();
                $savedIds = $resourceModel->saveBatch($filters);
                $resourceModel->deleteBatchOtherThan($savedIds);
                $message = Text::plural('COM_JFILTERS_N_ITEMS_GENERATED', count($savedIds));
                $this->app->enqueueMessage($message, 'message');
            }
        }
        return parent::display($cachable, $urlparams);
    }

    /**
     * Check if the necessary requirements are set for the extension to work.
     * If they are not met, print the relevant messages.
     *
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function systemCheck()
    {
        // Health check only on filters view.
        if ($this->input->get('view', 'filters', 'word') == 'filters') {

            // The joomla plugin that carries out the finder indexing.
            $contentFinderPluginEnabled = $this->isPluginEnabled('content', 'finder');

            // The Jfilters plugin that creates the indexes necessary for the results.
            $finderJFiltersIndexerPluginEnabled = $this->isPluginEnabled('finder', 'jfiltersindexer');

            // The plugin that generates filters form the subform fields.
            $jfiltersFieldSubformPluginEnabled = $this->isPluginEnabled('jfilters', 'fieldsubform');

            // Triggered onContentAfterSave. Among others creates the necessary field records for the field subform.
            $contentJfiltersPluginEnabled = $this->isPluginEnabled('content', 'jfilters');

            // No need to check for indexing if the plugins that handle it, are disabled.
            if ($contentFinderPluginEnabled && $finderJFiltersIndexerPluginEnabled) {
                $this->needsContentIndexing();
            }

            // If those plugins are enabled, check if the subform field values need indexing.
            if($jfiltersFieldSubformPluginEnabled && $contentJfiltersPluginEnabled) {
                $this->needsSubformFieldsIndexing();
            }
        }
    }

    /**
     * Check if a plugin is active and print the relevant message with a CTA.
     *
     * @param string $type
     * @param string $name
     *
     * @return bool
     * @throws \Exception
     * @since 1.0.0
     */
    protected function isPluginEnabled(string $type, string $name): bool
    {
        $isEnabled = true;
        if (!PluginHelper::isEnabled($type, $name)) {
            $isEnabled = false;
	        $pluginId = JfPluginHelper::getPluginId($type, $name);
            $pluginName = $this->translatePluginName($type, $name);
            $message = Text::sprintf('COM_JFILTERS_PLUGIN_MUST_BE_ENABLED', $pluginName);
            if($pluginId)
            {
            	$modalId = 'plugin'.$pluginId.'Modal';
	            $modalUrl = Route::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $pluginId . '&tmpl=component&layout=modal');
	            $modalTitle = Text::_('COM_JFILTERS_EDIT_PLUGIN_SETTINGS');
	            LayoutHelper::setModal($modalId, $modalTitle, $modalUrl);
	            $actionUrl   = '#'.$modalId;
	            $actionLabel = Text::_("JLIB_HTML_PUBLISH_ITEM");
	            $this->enqueueMessage($message, $actionUrl, $actionLabel);
            }
        }
        return $isEnabled;
    }

    /**
     * Check if we need to index the subform field values.
     *
     * @return bool
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function needsSubformFieldsIndexing(): bool
    {
        PluginHelper::importPlugin('content');
        $needsIndexing = \PlgContentJfilters::isSubformFieldValueNeedsIndexing();
        if($needsIndexing) {
            $pluginName = $this->translatePluginName('fields', 'subform');
            $modalId = 'com_jfilters_Subformfield_Index_Modal';
            $modalUrl = Route::_('index.php?option=com_jfilters&view=subform&tmpl=component');
            $modalTitle = Text::_('COM_JFILTERS_INDEX_SUBFORMFIELD_HEADER');
            LayoutHelper::setModal($modalId, $modalTitle, $modalUrl, false);
            $message = Text::sprintf("COM_JFILTERS_FINDER_NEEDS_INDEX", $pluginName);
            $actionUrl = '#' . $modalId;
            $actionLabel = Text::_("COM_JFILTERS_INDEX");
            $this->enqueueMessage($message, $actionUrl, $actionLabel);
        }
        return $needsIndexing;
    }

    /**
     * Check if the context's items need to be re-indexed.
     *
     * @return bool
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function needsContentIndexing(): bool
    {
        /** @var ConfigContextCollection $contextConfigCollection */
        $contextConfigCollection = ObjectManager::getInstance()->getObject(ConfigContextCollection::class);
        $contextToBeIndexed      = [];
        if ($contextConfigCollection->getSize() > 0) {
            PluginHelper::importPlugin('finder');
            /** @var ContextInterface $context */
            foreach ($contextConfigCollection as $context) {
                $needsIndexing = \PlgFinderJfiltersindexer::needsIndexing($context);
                if ($needsIndexing === true) {
                    $contextToBeIndexed[] = $context->getAlias();
                }
            }
        }

        if (!empty($contextToBeIndexed)) {
            $modalId = 'com_jfilters_Index_Modal';
            $modalUrl = Route::_('index.php?option=com_jfilters&view=index&tmpl=component');
            Factory::getApplication()->getLanguage()->load('com_finder');
            $modalTitle = Text::_('COM_FINDER_HEADING_INDEXER');
            LayoutHelper::setModal($modalId, $modalTitle, $modalUrl, false);
            $contextToBeIndexed = array_map(
                function ($string) {
                    return str_pad($string, strlen($string) + 2, "'", STR_PAD_BOTH);
                },
                $contextToBeIndexed
            );
            $contexstString = implode(
                ' ' . strtolower(Text::_('COM_FINDER_QUERY_OPERATOR_AND')) . ' ',
                $contextToBeIndexed
            );
            $message = Text::sprintf("COM_JFILTERS_FINDER_NEEDS_REINDEX", $contexstString);
            $actionUrl = '#' . $modalId;
            $actionLabel = Text::_("COM_JFILTERS_FINDER_REINDEX");
            $this->enqueueMessage($message, $actionUrl, $actionLabel);
        }

        return !empty($contextToBeIndexed);
    }

    /**
     * Print a message with a CTA.
     *
     * @param $message
     * @param string $actionUrl
     * @param string $actionLabel
     * @param boolean $isModal
     * @param string $type
     * @return $this
     * @since 1.0.0
     */
    protected function enqueueMessage(
        $message,
        $actionUrl = '',
        $actionLabel = '',
        $isModal = true,
        $type = CMSApplicationInterface::MSG_WARNING
    ) {
        $messageHtml = '<div class="d-flex"><span>' . $message .'</span>';
        if ($actionUrl && $actionLabel) {
        	$attributes = $isModal ? 'data-bs-toggle="modal"' : '';
            $messageHtml .= '<button type="button" class="btn btn-primary ms-4" data-bs-target="' . $actionUrl . '" '.$attributes.'>' . $actionLabel . '</button>';
        }
        $messageHtml .= '</div>';
        $this->app->enqueueMessage($messageHtml, $type);
        return $this;
    }

    /**
     * Translate the plugin name.
     *
     * @param string $group
     * @param string $name
     * @return string
     * @since 1.0.0
     */
    protected function translatePluginName($group, $name)
    {
        $lang = Factory::getApplication()->getLanguage();
        $source = JPATH_PLUGINS . '/' . $group . '/' . $name;
        $extension = 'plg_' . $group . '_' . $name;
        $lang->load($extension . '.sys', JPATH_ADMINISTRATOR)
        || $lang->load($extension . '.sys', $source);
        return Text::_($extension);
    }
}
