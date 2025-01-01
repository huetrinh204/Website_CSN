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
 * Define the css class that the selectable elements will take. Links don't need additional class.
 */
$elementModifierClass = '';

/**
 * Define if a dummy input (e.g radio, checkbox) will be printed.
 */
$prependDummyInput = false;

/**
 * Define if the parent nodes should be excluded from that display.
 */
$excludeParents = false;

/**
 * Call the same (universal) layout for all the dummy inputs.
 * In case of a template override, remove the following line of php and copy the contents of the '_dummy_elements.php',
 * after that line in your override for that layout.
 */
require ModuleHelper::getLayoutPath('mod_jfilters_filters', '_dummy_elements');