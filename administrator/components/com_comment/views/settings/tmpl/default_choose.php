<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       15.02.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');


echo CompojoomHtmlCtemplate::getHead(CcommentHelperMenu::getMenu(), 'settings', 'Create new config', '');
?>
	<div class="alert alert-info">
		<?php echo JText::sprintf('COM_COMMENT_REFER_TO_DOCUMENTATION_FOR_INTEGRATION', 'https://compojoom.com/support/documentation/ccomment'); ?>
		<?php if (!CCOMMENT_PRO): ?>
			(<?php echo JText::_('COM_COMMENT_CORE_VERSION_NOT_ALL_PLUGINS'); ?>)
		<?php endif; ?>
	</div>
	<div class="row">
		<div class="box-info">
			<p>
				<?php echo JText::_('COM_COMMENT_SELECT_COMPONENT'); ?>
			</p>

			<form id="adminForm" action="<?php echo JRoute::_('index.php?option=com_comment&task=settings.edit'); ?>"
			      method="post">

				<div class="control-group ccomment-group">
					<?php echo $this->plugins; ?>

					<button class="btn btn-primary"><?php echo JText::_('COM_COMMENT_NEXT'); ?></button>

				</div>

				<?php echo JHtml::_('form.token'); ?>

			</form>
		</div>
	</div>
<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(CcommentHelperBasic::getFooterText());