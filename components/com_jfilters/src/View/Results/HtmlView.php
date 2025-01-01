<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Site\View\Results;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Helper\OptionsHelper;
use Bluecoder\Component\Jfilters\Administrator\Model\ComponentConfig;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Field\Request;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as FilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule;
use Bluecoder\Component\Jfilters\Administrator\Model\SortingRule\Collection as SortingRuleCollection;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Router\Route;
use Joomla\Component\Finder\Site\View\Search\HtmlView as FinderHtmlView;

class HtmlView extends FinderHtmlView
{
    /**
     * @var FilterCollection
     * @since 1.0.0
     */
    protected $filtersCollection;

    /**
     * @var ObjectManager
     * @since 1.0.0
     */
    protected $objectManager;

    /**
     * @var string
     * @since 1.5.0
     */
    protected $pageTitle;

    /**
     * @var array
     * @since 1.16.0
     */
    protected $sortOrderFields;

    /**
     * Keep this public. It is called by our YT plugin.
     * @var MenuItem|null
     * @since 1.15.0
     */
    public ?MenuItem $menuItem = null;

    /**
     * @param null $tpl
     *
     * @throws \ReflectionException
     * @throws \Exception
     * @since 1.0.0
     */
    public function display($tpl = null)
    {
        $this->objectManager = ObjectManager::getInstance();
        $app = Factory::getApplication();
        $this->params = $app->getParams();
        $this->setDefaultParams();

        // Get the view data
        $this->state = $this->get('State');
        $this->query = $this->get('Query');
        \JDEBUG ? Profiler::getInstance('Application')->mark('afterJFiltersQuery') : null;
        $this->results = $this->get('Items');
        \JDEBUG ? Profiler::getInstance('Application')->mark('afterJFiltersResultsGenerate') : null;
        $this->total = $this->get('Total');
        \JDEBUG ? Profiler::getInstance('Application')->mark('afterJFiltersTotalCount') : null;
        $this->sortOrderFields = $this->get('sortFields');
        \JDEBUG ? Profiler::getInstance('Application')->mark('afterJFiltersSortFieldsGenerate') : null;

        /*
         * We have to get the `$this->filtersCollection` before the call of the `$this->formatPagination()`
         * since we use the `$this->filtersCollection` in that fn
         */
        $this->filtersCollection = $this->objectManager->getObject(FilterCollection::class);
        $this->filtersCollection->addCondition('filter.state', [1, 2]);
        if (Multilanguage::isEnabled()) {
            $this->filtersCollection->addCondition('filter.language',
                [Factory::getApplication()->getLanguage()->getTag(), '*'], '=');
        }
        $this->pagination = $this->get('Pagination');
        $this->formatPagination();

        \JDEBUG ? Profiler::getInstance('Application')->mark('afterJFiltersPagination') : null;

        // Run an event on each result item
        if (is_array($this->results)) {
            // Import Finder plugins
            PluginHelper::importPlugin('finder');
            foreach ($this->results as $result) {
                $app->triggerEvent('onFinderResult', array(&$result, &$this->query));
            }
        }

        // This is required for fetching properly the web assets in the layouts
        $wa = $this->document->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_finder');

        // We are using the layouts of com_finder's search view
        $layoutPath = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_finder' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . 'search';
        $templatePath = JPATH_THEMES . DIRECTORY_SEPARATOR . $app->getTemplate() . '/html/com_finder/search';
        $this->addTemplatePath($layoutPath);
        $this->addTemplatePath($templatePath);

        /*
         * We use this in our YOOtheme plugin
         */
        $this->menuItem = Factory::getApplication()->getMenu()->getActive();

        /*
         * Also we are using the layouts of com_jfilters' results view.
         * Keep that order between com_finder and com_jfilters it uses LIFO in the layout check.
         * This way the com_jfilters layouts have higher priority and are checked 1st.
         */
        $layoutPath = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jfilters' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . 'results';
        $templatePath = JPATH_THEMES . DIRECTORY_SEPARATOR . $app->getTemplate() . '/html/com_jfilters/results';
        $this->addTemplatePath($layoutPath);
        $this->addTemplatePath($templatePath);
        $this->prepareDocument();

        // Profile
        \JDEBUG ? Profiler::getInstance('Application')->mark('beforeJFiltersLayout') : null;

        \Joomla\CMS\MVC\View\HtmlView::display($tpl);

        \JDEBUG ? Profiler::getInstance('Application')->mark('afterJFiltersLayout') : null;
    }

    /**
     *  Joomla 4.4.7 and 5.1.3 introduced the pagination fn: `setAdditionalUrlParam(string $varName, $var)`
     *  see: https://github.com/joomla/joomla-cms/pull/43953
     *  The pagination blocks any variables in the pagination urls,
     *  beyond the standard (e.g. view, option, format,Itemid, tmpl, layout, task). see: https://github.com/joomla/joomla-cms/pull/43954/files
     *  and any other should be added with `setAdditionalUrlParam`
     *
     * @return void
     * @throws \Exception
     * @since 1.16.0
     */
    protected function formatPagination()
    {
        if (method_exists($this->pagination, 'setAdditionalUrlParam')) {
            // Remove the "tmpl=component" from the pagination links when ajax is used.
            $this->pagination->setAdditionalUrlParam('tmpl', null);
            // We also have to remove it from the input vars, as it may be created dynamically from within the Pagination::_buildDataObject().
            Factory::getApplication()->getInput()->set('tmpl', null);

            $filtersWithSelection = $this->filtersCollection->getSelectedItems();
            /** @var FilterInterface $filter */
            foreach ($filtersWithSelection as $filter) {
                $values =  array_map(function ($value) {
                    return $value !== null ? urlencode($value) : '';
                }, $filter->getRequest());
                $this->pagination->setAdditionalUrlParam($filter->getRequestVarName(), $values);
            }
            /*
             * --- Finder Vars --
             */
            // set the query if exists
            if ($searchQuery = Factory::getApplication()->getInput()->getString('q')) {
                $this->pagination->setAdditionalUrlParam('q', urlencode($searchQuery));
            }
            // Get the static taxonomy filters.
            if ($taxonomy = Factory::getApplication()->getInput()->getInt('f')) {
                $this->pagination->setAdditionalUrlParam('f', $taxonomy);
            }

            // Get the dynamic taxonomy filters.
            if ($taxonomyDynamic = Factory::getApplication()->getInput()->getInt('t')) {
                $this->pagination->setAdditionalUrlParam('t', $taxonomyDynamic);
            }

            // Get the language.
            if ($language = Factory::getApplication()->getInput()->getCmd('l')) {
                $this->pagination->setAdditionalUrlParam('l', $language);
            }

            // Get the start date and start date modifier filters.
            $var = 'd1';
            if ($d1 = Factory::getApplication()->getInput()->getString($var)) {
                $this->pagination->setAdditionalUrlParam($var, $d1);
            }

            $var = 'w1';
            if ($w1 = Factory::getApplication()->getInput()->getString($var)) {
                $this->pagination->setAdditionalUrlParam($var, $w1);
            }

            // Get the end date and end date modifier filters.
            $var = 'd2';
            if ($d2 = Factory::getApplication()->getInput()->getString($var)) {
                $this->pagination->setAdditionalUrlParam($var, $d2);
            }

            $var = 'w2';
            if ($w2 = Factory::getApplication()->getInput()->getString($var)) {
                $this->pagination->setAdditionalUrlParam($var, $w2);
            }

            /*
             * -- Ordering --
             */
            /** @var SortingRuleCollection $sortingRuleCollection */
            $sortingRuleCollection = ObjectManager::getInstance()->getObject(SortingRuleCollection::class);
            $sortingRules = $sortingRuleCollection->getItems();
            $currentSortingRule = $sortingRuleCollection->getCurrent();
            $isSearch = Factory::getApplication()->getInput()->getString('q');
            // Apply only if needed. If it's the 1st rule and is the current, will be applied anyway.
            if ($sortingRules && $currentSortingRule != reset($sortingRules)) {
                $this->pagination->setAdditionalUrlParam('o', $currentSortingRule->getSortField()->getFieldName());
                if ((!$isSearch && SortingRule::DEFAULT_SORTING_FILTERING_DIR != $currentSortingRule->getSortDirection())
                    || ($isSearch && SortingRule::DEFAULT_SORTING_SEARCH_DIR != $currentSortingRule->getSortDirection())) {
                    $this->pagination->setAdditionalUrlParam('od', strtolower($currentSortingRule->getSortDirection()));
                }
            }
        }
    }

    /**
     * Set default params for params used by the layouts.
     *
     * We do not want all the functionalities provided by the smart search page(e.g. search form)
     *
     * @since 1.0.0
     */
    protected function setDefaultParams()
    {
        $this->params->set('show_search_form', false);
    }

    /**
     * Prepares the document
     *
     * @throws \Exception
     * @since 1.0.0
     */
    protected function prepareDocument()
    {
        // Load the page's language.
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_finder');

        /*
         * We need to load awsomeplete here,
         * until that PR is accepted.
         * https://github.com/joomla/joomla-cms/pull/34715
         */
        $wa = $this->document->getWebAssetManager();
        $wa->usePreset('awesomplete');

        // Set page properties
        $this->setPageTitle();
        $this->setMetaDescription();
        $this->setPageCanonical();
        $this->setPathway();

        // Set robots
        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }

        $scriptOtions = ['title' => $this->getPageTitle()];
        Factory::getApplication()->getDocument()->addScriptOptions('jfilters.results', $scriptOtions);
    }

    /**
     * Set a meta description for that page.
     *
     * @throws \Exception
     * @since 1.0.0
     */
    protected function setMetaDescription()
    {
        if (!empty($this->query->input)) {
            return;
        }
        $metaDescriptionSet = false;
        $optionsHelper = OptionsHelper::getInstance();

        // Get the filters that have a selection.
        $selectedFilters = $this->filtersCollection->getSelectedItems();
        if(count($selectedFilters) == 1) {
            $selectedFilter = reset($selectedFilters);
        }

        /** @var FilterInterface $filter */
        foreach ($this->filtersCollection as $filter) {
            // Pathways apply only when a filter has a single request var, is root or when only one filter is selected.
            if ($metaDescriptionSet === false && count($filter->getRequest()) == 1 && ($filter->getRoot() || (isset($selectedFilter) && $selectedFilter == $filter))) {
                /** @var OptionInterface[] $selectedOptions */
                $selectedOptions = array_values($optionsHelper->getSelectedOptions($filter));
                if (count($selectedOptions) == 1 && $selectedOptions[0]->getMetadescription()) {
                    $this->document->setDescription($selectedOptions[0]->getMetadescription());
                    $metaDescriptionSet = true;
                }
                break;
            }
        }

        if ($metaDescriptionSet === false) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }
    }

    /**
     * Set a pathway
     *
     * @throws \Exception
     * @since 1.0.0
     */
    protected function setPathway()
    {
        $app = Factory::getApplication();
        $pathItems = [];
        $optionsHelper =OptionsHelper::getInstance();
        $selectedOption = false;

        // Set pathway for filter
        /** @var FilterInterface $filter */
        foreach ($this->filtersCollection as $filter) {
            $selectedOptions = $optionsHelper->getSelectedOptions($filter);
            // Pathways apply only to root filters with a single selection.
            if ($filter->getRoot() && count($selectedOptions) == 1) {
                /** @var OptionInterface $selectedOption */
                $selectedOption = reset($selectedOptions);
                if ($selectedOption) {
                    $pathItems [] = ['label' => $selectedOption->getLabel(), 'link' => ''];
                    /*
                     * We are using the url's 'max_path_nesting_levels' to fetch the levels of the pathway.
                     * Maybe a dedicated setting for that should exist.
                     */
                    $levelsDepthMax = $filter->getAttributes()->get('max_path_nesting_levels');
                    $currentLevel = 1;
                    while (method_exists($selectedOption, 'getParentOption') && $selectedOption->getParentOption() !== null && $currentLevel < $levelsDepthMax) {
                        $selectedOption = $selectedOption->getParentOption();
                        $pathItems [] = [
                            'label' => $selectedOption->getLabel(),
                            'link' => $selectedOption->getLink()
                        ];
                        $currentLevel ++;
                    }
                }
                break;
            }
        }

        // Set pathway for search
        if (!empty($this->query->input)) {
            $link = '';
            if ($selectedOption && $filter) {
                $link = $optionsHelper->getClearOption($filter)->getLink();
            }
            $pathItems [] = ['label' => $this->query->input, 'link' => $link];
        }

        $pathItems = array_reverse($pathItems);
        foreach ($pathItems as $pathItem) {
            $app->getPathway()->addItem($this->escape($pathItem['label']), $pathItem['link']);
        }
    }

    /**
     * Get the page's title by the selected filters
     *
     * @return string
     * @throws \ReflectionException
     * @since 1.5.0
     */
    protected function getPageTitle()
    {
        if ($this->pageTitle === null) {
            $app = Factory::getApplication();
            $optionsHelper = OptionsHelper::getInstance();
            $searchQuery = utf8_trim($app->getInput()->getString('q', ''));
            $titleElements = $searchQuery ? [$searchQuery] : [];

            /** @var FilterInterface $filter */
            foreach ($this->filtersCollection as $filter) {
                $selectedOptions = $optionsHelper->getSelectedOptions($filter);
                if (
                    $filter->getAttributes()->get('show_in_page_title', $this->params->get('show_in_page_title', true))
                    && !empty($selectedOptions)
                ) {
                    $labelsToShow = [];
                    foreach ($selectedOptions as $selectedOption) {
                        $labelsToShow[] = strip_tags($selectedOption->getLabel());
                    }

                    !empty($labelsToShow) ? $titleElements[] = implode(' ' . Text::_('COM_FINDER_QUERY_OPERATOR_OR') . ' ', $labelsToShow) : false;
                }
            }

            $menuItemTitle = $this->params->get('page_title', $this->menuItem ? $this->menuItem->title : '');

            if ($titleElements) {
                /** @var  ComponentConfig $componentConfig */
                $componentConfig = $this->objectManager->getObject(ComponentConfig::class);
                $separator = $componentConfig->get('title_separator', ' ');

                // Also language constants can be used as separators (e.g. and, or).
                if (utf8_strlen($separator) > 1) {
                    $separator = Text::_($separator);
                }

                // If not a space pad with spaces.
                if ($separator != ' ') {
                    $separator = utf8_trim($separator);
                    $separator = utf8_str_pad($separator, utf8_strlen($separator) + 2, ' ', STR_PAD_BOTH);
                }
                $this->pageTitle = implode($separator, $titleElements);
                // Append selections to page title or not
                $this->pageTitle = $this->pageTitle && $this->params->get('append_in_menu_item_title', 0) ? $menuItemTitle . $separator . $this->pageTitle : $this->pageTitle;
            }

            if (empty($this->pageTitle)) {
                $this->pageTitle = $menuItemTitle;
            }
        }

        return $this->pageTitle;
    }

    /**
     * Set the page's title by the selected filters
     * @throws \ReflectionException
     * @since 1.0.0
     */
    protected function setPageTitle()
    {
        //$this->menuItem
        $this->setDocumentTitle($this->getPageTitle());
    }

    /**
     * Set the page's canonical tag
     *
     * @throws \Exception
     * @since 1.0.0
     */
    protected function setPageCanonical()
    {
        $optionsHelper = OptionsHelper::getInstance();

        // Get the filters that have a selection.
        $selectedFilters = $this->filtersCollection->getSelectedItems();
        if(count($selectedFilters) == 1) {
            $selectedFilter = reset($selectedFilters);
        }

        /** @var FilterInterface $filter */
        foreach ($this->filtersCollection as $filter) {
            $canonical_url = '';

            // Canonicals apply only to root filters or when single filter is selected, with a single request var.
            if (count($filter->getRequest()) == 1 && ($filter->getRoot() || (isset($selectedFilter) && $filter == $selectedFilter))) {
                $selectedOptions = $optionsHelper->getSelectedOptions($filter);
                if(count($selectedOptions) != 1) {
                    continue;
                }
                /** @var OptionInterface $selectedOption */
                $selectedOption = reset($selectedOptions);

                /**
                 * Set alternative canonical
                 * If the filter has the 'use_canonical' param enabled, has alternative requests
                 * set a canonical
                 */
                if (
                    $filter->getConfig()->getValue()->getRequests() &&
                    $filter->getAttributes()->get('use_canonical') &&
                    $alternativeRequests = $filter->getConfig()->getValue()->getRequests()->getChildren()) {
                    /*
                     * atm the 1st request param, is regarded canonical.
                     * @todo set a canonical property to the request param in the filters configuration.
                     */
                    /** @var Request $canonicalRequest */
                    $canonicalRequest = reset($alternativeRequests);
                    if ($canonicalRequest->getExtension() && $canonicalRequest->getView() && $canonicalRequest->getView() && $canonicalRequest->getValue()) {
                        $uri = 'index.php?option=' . $canonicalRequest->getExtension() . '&view=' . $canonicalRequest->getView() . '&' . $canonicalRequest->getValue(
                            ) . '=' . $selectedOption->getValue();
                        $canonical_url = Route::_($uri, true, Route::TLS_IGNORE, true);

                        // If the url is not seo friendly check for a menu item.
                        if (strpos($canonical_url, '/component/') !== false || ($this->params->get('sef', 1) && strpos($canonical_url, 'view=') !== false)) {
                            $menuItem = $this->findMenuItem($canonicalRequest, $selectedOption->getValue());
                            if ($menuItem) {
                                $uri .= '&Itemid=' . (int)$menuItem->id;
                                $canonical_url = Route::_($uri, true, Route::TLS_IGNORE, true);
                            }
                            // Do not set a canonical if there is no menu item (sef alias) set for such page
                            else {
                                $canonical_url = '';
                            }
                        }
                    }
                }

                // Use as canonical the filter's url. We only want the root filter's results to be indexed.
                if (empty($canonical_url)) {
                    $uri = $selectedOption->getLink(true, false);
                    if($this->menuItem->component == 'com_jfilters') {
                        $uri->setVar('Itemid', $this->menuItem->id);
                    }
                    $canonical_url = Route::_($uri, true, Route::TLS_IGNORE, true);
                }
                break;
            }
        }

        if($canonical_url) {
            $this->document->_links = [];
            // add a new one
            $this->document->_links[$canonical_url] = [
                'relType' => 'rel',
                'relation' => 'canonical',
                'attribs' => ''
            ];
        }
    }

    /**
     * Find a menu item for that request.
     *
     * @param   Request  $canonicalRequest
     * @param   string   $id
     *
     * @return false|mixed
     * @throws \Exception
     * @since 1.0.0
     */
    protected function findMenuItem(Request $canonicalRequest, $id)
    {
        $toBereturned = false;
        $menu = Factory::getApplication()->getMenu();
        $menuItems = $menu->getItems('component', $canonicalRequest->getExtension());
        foreach ($menuItems as $menuItem) {
            if ($menuItem->query['view'] == $canonicalRequest->getView()
                && (empty($menuItem->query[$canonicalRequest->getValue()]) || $menuItem->query[$canonicalRequest->getValue()] == $id)) {
                $toBereturned = $menuItem;
                break;
            }
        }

        return $toBereturned;
    }
}
