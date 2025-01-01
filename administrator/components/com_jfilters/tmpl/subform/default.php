<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

Text::script('COM_FINDER_INDEXER_MESSAGE_COMPLETE', true);

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useStyle('com_jfilters.index')
	->useScript('com_jfilters.subform.index');

?>

<div class="text-center">
	<h1 id="jfilters-progress-header" class="m-t-2" aria-live="assertive"><?php echo Text::_('COM_JFILTERS_INDEX_SUBFORMFIELD_HEADER'); ?></h1>
	<p id="jfilters-progress-message" aria-live="polite"><?php echo Text::_('COM_JFILTERS_INDEX_SUBFORMFIELD_HEADER'); ?></p>
	<div id="progress" class="progress">
		<div id="progress-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
	</div>
	<input id="jfilters-index-token" type="hidden" name="<?php echo Factory::getApplication()->getSession()->getFormToken(); ?>" value="1">
</div>
