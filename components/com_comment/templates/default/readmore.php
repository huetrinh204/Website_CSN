<?php
/**
 * @package    Ccomment
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       24.04.17
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access'); ?>

<a href="<?php echo $this->link; ?>#ccomment" class="btn btn-primary ccomment-readmore">
	<?php if (!$this->discussionClosed) : ?>
		<?php echo JText::_('COM_COMMENT_WRITECOMMENT'); ?>
		(<?php echo $this->count; ?> <?php echo $this->commentTranslation; ?>)
	<?php else: ?>
		<?php echo $this->count; ?> <?php echo $this->commentTranslation; ?>
	<?php endif; ?>

</a>


<?php if ($this->config->get('template_params.preview_visible', 0)) : ?>
	<?php echo $this->loadTemplate('preview'); ?>
<?php endif; ?>
