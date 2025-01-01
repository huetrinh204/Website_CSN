<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$document = Factory::getApplication()->getDocument();
$document->getWebAssetManager()->useScript('mod_jfilters_tooltip');

$tooltipContent = $tooltipContent ?? 0;
$tooltipId = $tooltipId ?? '';
?>

<div class="jfilters-filter__tooltip" role="tooltip" tabindex="-1" <?= !empty($tooltipId) ? 'id="' . $tooltipId . '"' : ''; ?>>
    <span class="jfilters-filter__tooltipContent">
        <?= $tooltipContent?>
    </span>
</div>
