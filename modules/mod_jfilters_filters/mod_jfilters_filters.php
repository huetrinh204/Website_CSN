<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

use Bluecoder\Module\JfiltersFilters\Site\Helper\FiltersHelper;
use Joomla\CMS\Helper\ModuleHelper;

$params->def('count', 10);
$filtersHelper = new FiltersHelper($params);
$filters = $filtersHelper->getList();

require ModuleHelper::getLayoutPath('mod_jfilters_filters', $params->get('layout', 'default'));
