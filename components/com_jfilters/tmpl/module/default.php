<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Helper\ModuleHelper;

$attributes['showtitle'] = 0;
$this->module->title = '';
echo ModuleHelper::renderModule($this->module, $attributes);