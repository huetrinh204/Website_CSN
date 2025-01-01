<?php
/**
 * @package    Ccomment
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       24.04.17
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

$user   = JFactory::getUser();
$config = $this->config;
?>

<script type="text/x-template" id="ccomment-menu">
	<div class="row-fluid ccomment-menu">
		<h4 class="pull-left">
			<?php echo JText::_('COM_COMMENT_COMMENTS_TITLE'); ?>
			(<span class="ccomment-comment-counter">{{pagination.total_with_children}}</span>)
		</h4>
		<div class="pull-right">
			<?php if (ccommentHelperSecurity::canPost($config) && !$this->discussionClosed) : ?>
				<button
						v-on:click="newComment"
						class="ccomment-add-new btn btn-mini" title='<?php echo JText::_('COM_COMMENT_ADDNEW'); ?>'>
					<?php echo JText::_('COM_COMMENT_ADDNEW'); ?>
				</button>
			<?php endif; ?>
			<?php // show the search only if it is on and we have comments in the item ?>
			<?php if ($config->get('template_params.show_search', 0) && $this->count) : ?>
				<button class="btn ccomment-search btn-mini" title='<?php echo JText::_('COM_COMMENT_SEARCH'); ?>'>
					<?php echo JText::_('COM_COMMENT_SEARCH'); ?>
				</button>
			<?php endif; ?>
			<?php if ($config->get('template_params.show_rss')) : ?>
				<a class="btn btn-mini ccomment-rss" target="_blank"
				   href='<?php echo JRoute::_("index.php?option=com_comment&component=" . $this->component . "&contentid=" . $this->contentId . '&format=feed') ?>'
				   title='<?php echo JText::_('COM_COMMENT_RSS'); ?>'>
					<?php echo JText::_('COM_COMMENT_RSS'); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</script>
