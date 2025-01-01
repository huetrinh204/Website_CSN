<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('com_jfilters.index');
?>
<div class="text-center">
    <h2><?php echo Text::_('COM_JFILTERS_FINDER_INDEX_DELETING') ?></h2>
    <i id="jfilters-index-spinner" class="fas fa-sync fa-spin fa-2x"></i>
    <div id="jfilters-index-message"></div>
    <input id="jfilters-index-token" type="hidden" name="<?php echo Factory::getSession()->getFormToken(); ?>" value="1">
</div>



