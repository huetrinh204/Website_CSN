<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\View\Filters;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextCollection;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Class HtmlView
 * @package Bluecoder\Component\Jfilters\Administrator\View\Filters
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  Form
     * @since 1.0.0
     */
    public $filterForm;

    /**
     * @var  array
     * @since  1.0.0
     */
    public $activeFilters;

    /**
     * @var  array
     * @since  1.0.0
     */
    protected $items;

    /**
     * @var  Pagination
     * @since  1.0.0
     */
    protected $pagination;

    /**
     * @var  CMSObject
     * @since  1.0.0
     */
    protected $state;

    /**
     * @var  string
     * @since  1.0.0
     */
    protected $sidebar;

    /**
     * @var ContextCollection
     */
    protected $contextCollection;

    /**
     * Execute and display a template script.
     *
     * @param null $tpl
     * @throws \Exception
     * @since 1.0.0
     */
    public function display($tpl = null)
    {
        $input = Factory::getApplication()->getInput();
        /*
        * If we do not declare the formPath, it can look up in the front-end JFilters component for the forms dir, which does not exist.
        * This will happen when we call this view from the front-end. E.g. Using the JFilters editor button.
        */
        Form::addFormPath(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jfilters' . DIRECTORY_SEPARATOR . 'forms');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Display only published and listening in modal. No need to show useless filters
        if ($this->getLayout() === 'modal') {
            $input->set('filter_state', [1,2]);

            $filters = $input->get('filter');

            if ($input->get('JFClient') === 'com_menus' && isset($filters['context'])) {
                unset($this->activeFilters['context']);
                $this->filterForm->removeField('context', 'filter');
            }

        }
        // We cannot have an array as 'filter.state' in the filters normal view (not modal)
        elseif (is_array($this->getModel()->getState('filter.state'))) {
            $input->set('filter_state', null);
        }
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        /** @var  ContextCollection $contextCollection */
        $this->contextCollection = ObjectManager::getInstance()->getObject(ContextCollection::class);

        // Check for errors.
        $errors = $this->get('Errors') ?? [];
        if (count($errors)) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Only add toolbar when not in modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();

            // We do not need to filter by language when multilingual is disabled
            if (!Multilanguage::isEnabled()) {
                unset($this->activeFilters['language']);
                $this->filterForm->removeField('language', 'filter');
            }
        }
        else {
            // Remove the State filter. We always load filters in 'published' and 'listening' state.
            unset($this->activeFilters['state']);
            $this->filterForm->removeField('state', 'filter');

            // We need to load the admin lang strings used by the modal window, which can be called from the front-end as well.
            Factory::getApplication()->getLanguage()->load('joomla', JPATH_ADMINISTRATOR);
            Factory::getApplication()->getLanguage()->load('com_jfilters', JPATH_ADMINISTRATOR);
        }

        parent::display($tpl);
    }

    /**
     * Adds the toolbar.
     *
     * @throws \Exception
     * @since 1.0.0
     */
    protected function addToolbar()
    {
        $component = 'com_jfilters';
        $canDo = ContentHelper::getActions('com_jfilters', 'component');

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        $title = Text::sprintf('COM_JFILTERS_FILTERS', Text::_(strtoupper($component)));

        // Prepare the toolbar.
        ToolbarHelper::title($title, 'jfilters');

        if ($canDo->get('jfilters.synchronize.filters')) {
            $toolbar->standardButton('refresh')
                    ->text('COM_JFILTERS_FILTERS_SYNCHRONIZE')
                    ->task('filters.synchronize');
        }

        if ($canDo->get('jfilters.generate.filters')) {
            $toolbar->standardButton('play')
                ->text('COM_JFILTERS_FILTERS_REGENERATE')
                ->task('filters.purge');
        }

        if ($canDo->get('core.edit.state') || $canDo->get('jfilters.generate.filters') || $canDo->get('core.delete')) {
            /** @var \Joomla\CMS\Toolbar\Button\DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('fa fa-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if ($canDo->get('core.edit.state')) {
                $childBar->publish('filters.publish')->listCheck(true);
                $childBar->unpublish('filters.unpublish')->listCheck(true);
                $childBar->standardButton('headphones')->text('COM_JFILTERS_FILTERS_LISTEN')->task('filters.archive');
            }

            if ($canDo->get('jfilters.generate.filters')) {
                $childBar->save2copy('filters.copy')->listCheck(true);
            }

            if ($canDo->get('core.delete')) {
                $childBar->delete('filters.delete')->listCheck(true);
            }
        }

        if (Factory::getApplication()->getIdentity()->authorise('core.admin')) {
            ToolbarHelper::checkin('filters.checkin');
        }

        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            ToolbarHelper::preferences($component);
        }

        ToolbarHelper::help('JHELP_COMPONENTS_FIELDS_FIELDS');
    }

    /**
     * Returns the sort fields.
     *
     * @return array
     * @since 1.0.0
     */
    protected function getSortFields()
    {
        return [
            'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
            'a.state' => Text::_('JSTATUS'),
            'a.name' => Text::_('JGLOBAL_TITLE'),
            'a.access' => Text::_('JGRID_HEADING_ACCESS'),
            'language' => Text::_('JGRID_HEADING_LANGUAGE'),
            'a.id' => Text::_('JGRID_HEADING_ID'),
        ];
    }
}
