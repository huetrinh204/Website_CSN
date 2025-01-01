<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Helper\OptionsHelper;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection\Filtered\Nested;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

$isMultiLanguage = Multilanguage::isEnabled();

/** @var  \Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface $filter */
$filter = $this->filter;

if ($filter && $filter->getState() > 0 && $filter->getOptions()->getSize() > 0) {
    Factory::getApplication()->getDocument()->getWebAssetManager()
           ->usePreset('choicesjs')
           ->useScript('webcomponent.field-fancy-select');

    $optionsArray = [];
    $options = $filter->getOptions();
    $options->setUseOtherSelectionsAsConditions(false);

    // in case of nested options we want the non nested version.
    if ($filter->getOptions()->getOptionConfig()->getIsTree() && $filter->getOptions() instanceof Nested) {
        $optionsHelper = OptionsHelper::getInstance();
        /** @var Nested $options */
        $options = $optionsHelper->getFullTree($options, 0, '-');
    }

    $urlBase = '';
    if ($isMultiLanguage && $filter->getLanguage() != '*') {
        $urlBase = '&lang=' . $filter->getLanguage();
    }
    $selected = [];

    /** @var \Bluecoder\Component\Jfilters\Administrator\Model\Filter\OptionInterface $option */
    foreach ($options as $option) {
        $url = $urlBase . '&' . $filter->getRequestVarName() . '[]=' . urlencode($option->getValue());
        $counter = $option->getCount() ?: 0;
        if ($option->isSelected()) {
            $selected [] = $url;
        }

        $label = $option->getLabel();
        /*
         * Only the root filters to have counter.
         * It is confusing to show counter for the others as they will not return what the counter says when crosschecked with others.
         */
        $label .= $filter->getRoot() ? ' (' . ($option->getCount() ?: 0) . ')' : '';
        $optionsArray[] = ['text' => $label, 'value' => $url];
    }

    $html = HTMLHelper::_('select.genericlist', $optionsArray, 'filter__options', 'multiple class="filter__options-select"',
        'value', 'text', $selected, 'filter_' . $filter->getId());
    Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');
    Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');
    $attributes = [
        'class="filter__options"',
        ' allow-custom',
        ' placeholder="' . $this->escape(Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS')) . '" ',
    ];
    ?>
<joomla-field-fancy-select <?= implode(' ', $attributes); ?>><?= $html; ?></joomla-field-fancy-select>
<?php
} else {?>
    <span><?= Text::_('COM_JFILTERS_NO_FILTER_OPTIONS'); ?></span>
<?php
}