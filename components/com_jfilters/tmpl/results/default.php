<?php
/**
 * This file is the same as the one encountered in components/com_finder/tmpl/search/default.php
 *
 * The only reason for it's existence is the removal of the search form from the results page.
 * This file uses the sub-layouts located under: components/com_finder/tmpl/search
 * Any template override of each of those sub-layouts, can be done either under com_finder or com_jfilters.
 * JFilters will detect and use the template overrides of com_finder if there are no such under com_jfilters
 *
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 * @since       1.5.3
 */

defined('_JEXEC') or die;

$this->document->getWebAssetManager()
	->useStyle('com_finder.finder')
	->useScript('com_finder.finder');

?>
<div class="com-finder finder">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<h1>
			<?php if ($this->escape($this->params->get('page_heading'))) : ?>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			<?php else : ?>
				<?php echo $this->escape($this->params->get('page_title')); ?>
			<?php endif; ?>
		</h1>
	<?php endif; ?>

	<?php // Load the search results layout if we are performing a search. ?>
	<?php if ($this->query->search === true) : ?>
		<div id="search-results" class="com-finder__results">
			<?php echo $this->loadTemplate('results'); ?>
		</div>
	<?php endif; ?>
</div>
