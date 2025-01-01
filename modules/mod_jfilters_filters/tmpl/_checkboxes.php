<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Helper\ModuleHelper;

/**
 * Define the css class that the selectable elements will take
 */
$elementModifierClass = 'jfilters-item-link--checkbox';

/**
 * Define if a dummy checkbox will be printed.
 */
$prependDummyInput = true;

/**
 * Define if the parent nodes should be excluded from that display.
 */
$excludeParents = true;

/**
 * Call the same (universal) layout for all the dummy inputs.
 * In case of a template override, remove the following line of php and copy the contents of the '_dummy_elements.php',
 * after that line in your override for that layout.
 */
require ModuleHelper::getLayoutPath('mod_jfilters_filters', '_dummy_elements');