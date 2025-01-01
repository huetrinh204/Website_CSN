<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Helper\OptionsHelper;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionFactory;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface;
use Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Bluecoder\Module\JfiltersFilters\Site\Helper\LayoutHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

if (empty($filters)) {
    return;
}

/**
 * The parameters exposed to the scripts.
 */
$filterParamNamesUsedByScripts = ['toggle_state', 'list_search', 'isTree', 'nested_toggle_state', 'parent_node_linkable'];
$optionsHelper = OptionsHelper::getInstance();

/** @var \Joomla\CMS\Document\Document $document */
$document = Factory::getApplication()->getDocument();
$wa = $document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('mod_jfilters_filters');
$wa->usePreset('mod_jfilters_filters');
$moduleLayoutHelper = LayoutHelper::getInstance();

// Load finder's css in every module's page, when ajax is active
/** @var Registry $params */
if ($params->get('ajax_mode', 0)) {
    $wa->getRegistry()->addExtensionRegistryFile('com_finder');
    $wa->useStyle('com_finder.finder');
    try {
        $menuItemJF = $moduleLayoutHelper->getMenuItem();
        if ($menuItemJF && $menuItemJF->getParams()->get('show_sort_order', 0)) {
            // Make sure that bs drop-down is preloaded, when the sorting drop-down exists.
            $wa->useScript('bootstrap.dropdown');
        }
    }catch (\Exception $exception) {
        //Suck it. No big deal
    }
}

$chevronSVG = file_get_contents(JPATH_ROOT . '/media/mod_jfilters_filters/images/chevron.svg');
$filterScriptOptions = [];
$filterStyle = [];
$filterHeaderHTMLElement = $params->get('header_html_element', 'h4');
/** @var stdClass $module */
?>

<div id="mod-jfilters_filters-<?php echo $module->id ?>" class="mod-jfilters_filters">
    <div class="jfilters-filters-container">
        <?php
        /**
         * @var  FilterInterface $filter
         */
        foreach ($filters as $filter) {

           /*
           * Pre-load the assets for the sub-layouts in case of ajax.
           * Ajax can call a filter which should be created at the runtime.
           * Its assets should be in the page, in order to work properly, even if the filter is not loaded beforehand.
           */
            if ($params->get('ajax_mode', 0)) {
                $layoutKey = '';
                $wa = $document->getWebAssetManager();
                if ($filter->getDisplay() == 'calendar') {
                    $wa->useStyle('flatpickr')->useScript('mod_jfilters_calendar');
                    // Load the lang strings, used by the scripts.
                    $moduleLayoutHelper->loadAsset($filter->getDisplay());
                }

                if ($filter->getDisplay() == 'range_inputs' || $filter->getDisplay() == 'range_inputs_sliders') {
                    $wa->useScript('range_inputs');
                    // Load the lang strings, used by the scripts.
                    $moduleLayoutHelper->loadAsset('range_inputs');
                }

                if ($filter->getDisplay() == 'range_sliders' || $filter->getDisplay() == 'range_inputs_sliders') {
                    $wa->useScript('mod_jfilters_tooltip')->usePreset('mod_jfilters_range_sliders');
                    $moduleLayoutHelper->loadAsset('range_sliders');
                }
            }

            $options = $filter->getOptions();
            /*
             * No visible OR (No options and is not a range) > No filter.
             */
            if (!$filter->isVisible() || ($options->getSize() == 0 && $filter->getIsRange() === false)) {
                continue;
            }
            $display = '_' . $filter->getDisplay();
            $ariaExpanded = $filter->getAttributes()->get('toggle_state', 'expanded') == 'expanded' ? 'true' : 'false';
            $filterExtraProperties = [];
            ?>
            <div id="jfilters-filter-container-<?php echo $module->id, '-', $filter->getId() ?>"
                 class="jfilters-filter-container">

                <<?=$filterHeaderHTMLElement;?> class="jfilters-filter-header">
                    <button class="jfilters-filter-header__toggle" type="button" aria-controls="jfilters-filter-container__inner-<?php echo $module->id, '-', $filter->getId() ?>"
                            aria-expanded="<?php echo  $ariaExpanded; ?>"><?php echo htmlspecialchars(Text::_($filter->getLabel()), ENT_QUOTES)?>
                        <span class="jfilters-filter-header__toggle-icon" aria-hidden="true"><?php echo $chevronSVG?></span>
                    </button>
                </<?=$filterHeaderHTMLElement;?>>

                <div id="jfilters-filter-container__inner-<?php echo $module->id, '-', $filter->getId() ?>"
                     class="jfilters-filter-container__inner"
                     aria-hidden="<?php echo $filter->getAttributes()->get('toggle_state', 'expanded') == 'expanded' ? 'false' : 'true' ?>">
                    <?php

                    // Load the list search layout
                    if ($filter->getAttributes()->get('list_search', false)
                            && !in_array($filter->getDisplay(), ['list', 'calendar', 'range_inputs', 'range_sliders', 'range_inputs_sliders'])){
                        $additionalListSearchClasses = '';
                        $listSearchPlaceholder = '';
                        require ModuleHelper::getLayoutPath('mod_jfilters_filters', '_list_search');
                    }

                    // Show the clear option if there is selection, the display is not list and is allowed by the settings
                    if ($filter->getAttributes()->get('show_clear_option', true)
                        && !empty($filter->getRequest()) && $filter->getDisplay() != 'list') {
                        $clearOption = $optionsHelper->getClearOption($filter, $params->get('Itemid', 0)); ?>
                        <a class="jfilters-item-link jfilters-item-link--clear" href="<?php echo Route::_($clearOption->getLink());?>"
                           rel="nofollow"><?php echo Text::_($clearOption->getLabel());?></a>
                        <?php
                    }

                    $roottListId = $listId =  'jfilters-filter-list-' . $module->id . '-' . $filter->getId();
                    require ModuleHelper::getLayoutPath('mod_jfilters_filters', $display);
                    ?>
                </div>
            </div>
            <?php

            // Script options that used by our scripts
            $filterScriptOptions[] = [
                'id' => $filter->getId(),
                'properties' => json_encode($filter->getAttributes($filterParamNamesUsedByScripts)),
                'moduleId' => $module->id,
                'extraProperties' => isset($filterExtraProperties[$filter->getId()]) ? $filterExtraProperties[$filter->getId()] : [],
            ];

            // Scrollbar css
            if ($filter->getAttributes()->get('scrollbar_after', 0) && !empty($roottListId) && $filter->getDisplay() != 'calendar') {
                $scrollbarAfter = $filter->getAttributes()->get('scrollbar_after', 0) . $filter->getAttributes()->get(
                        'scrollbar_after_unit',
                        'px'
                    );
                $filterStyle[] = <<<CSS
                #$roottListId { overflow:auto; height:auto; max-height:{$scrollbarAfter};position: relative;}
CSS;
            }
        } ?>
    </div>
    <?php
    // Add the submit button
    if ($params->get('submit_filters_using_button', 0)) {
        // Get the selected filters' url by creating a dummy Option in a dummy Filter
        $optionFactory = ObjectManager::getInstance()->getObject(OptionFactory::class);
        /** @var OptionInterface $option */
        $dummyOption = $optionFactory->create([], OptionInterface::class);
        $dummyOption->setValue('');
        $dummyFilter = clone $filter;
        // It has to be multi-select and NOT a root and NOT a range.
        $dummyFilter->setDisplay('checkboxes');
        $dummyFilter->setRoot(false);
        $dummyOption->setParentFilter($dummyFilter);
        $dummyOption->getLink(false)->setVar('Itemid', (int)$params->get('Itemid', 0));
        ?>
    <button type="button" class="mod-jfilters_filters__submit-btn btn btn-primary w-100 mt-4" data-url="<?= Route::_($dummyOption->getLink()); ?>">
        <span class="jfilters_button__label">
            <?= Text::_('MOD_JFILTERS_FILTERS_SHOW_RESULTS_BTN_LABEL');?>
        </span>
        <?php require ModuleHelper::getLayoutPath('mod_jfilters_filters', '_blinking_dots'); ?>
    </button>
    <?php }?>
</div>

<?php
// Add the necessary script variables for the js functionality
if ($filterScriptOptions) {
    // Merge any previous set options of the same type (from multiple jfilters_filters modules).
    $filterScriptOptionsSet = Factory::getApplication()->getDocument()->getScriptOptions('jfilters.filter');
    $filterScriptOptions = !empty($filterScriptOptionsSet) ? array_merge($filterScriptOptionsSet, $filterScriptOptions) : $filterScriptOptions;
    Factory::getApplication()->getDocument()->addScriptOptions('jfilters.filter', $filterScriptOptions);
    // Module options
    $isYOOthemeTemplate = Factory::getApplication()->getTemplate(true)->params->get('yootheme', 'false') === 'true';
    $moduleScriptOptionsSet = Factory::getApplication()->getDocument()->getScriptOptions('jfilters.filteringModule');
    $moduleScriptOptions = [['id' => $module->id,'ajax_mode' => $params->get('ajax_mode', 0), 'isYOOthemeTemplate' => (int)$isYOOthemeTemplate, 'submit_filters_using_button' => (int)$params->get('submit_filters_using_button', 0)]];
    $moduleScriptOptions = !empty($moduleScriptOptionsSet) ? array_merge($moduleScriptOptionsSet, $moduleScriptOptions) : $moduleScriptOptions;
    Factory::getApplication()->getDocument()->addScriptOptions('jfilters.filteringModule', $moduleScriptOptions);
}

// Add inline styles to the document.
if ($filterStyle) {
    $wa->addInlineStyle(implode('', $filterStyle), [], ['id' => 'mod_jfilters_filters_' . $module->id]);
}
