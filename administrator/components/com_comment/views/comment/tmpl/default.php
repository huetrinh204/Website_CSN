<?php
/**
 * @package    CComment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       05.01.15
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');

JtoolbarHelper::apply('comment.apply');
JtoolbarHelper::save('comment.save');
JToolbarHelper::cancel('comment.cancel');

if (version_compare(JVERSION, '4.0', 'lt')) {
	CompojoomHtmlBehavior::bootstrap(false, true);
}
echo CompojoomHtmlCtemplate::getHead(CcommentHelperMenu::getMenu(), 'comments', 'COM_COMMENT_EDIT_COMMENT', '');
?>


	<div class="box-info">
		<form id="adminForm" name="adminForm" method="post"
		      action="<?php echo JRoute::_('index.php?option=com_comment&layout=edit&id=' . $this->item->id); ?>">
			<div class="form-group">
				<?php echo $this->form->getLabel('comment') ?>
				<?php echo $this->form->getInput('comment'); ?>
			</div>
			<div class="form-horizontal">
				<div class="col-sm-6">
					<div class="form-group">
						<?php echo $this->form->getLabel('id'); ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('id'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('published') ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('published'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('component') ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('component'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('userid') ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('userid'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('name') ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('name'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('notify') ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('notify'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('parentid') ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('parentid'); ?>
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<?php echo $this->form->getLabel('contentid') ?>

						<div class="col-sm-10">
							<?php echo $this->form->getInput('contentid'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('ip') ?>

						<div class="col-sm-10">
							<?php echo $this->form->getInput('ip'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('date') ?>

						<div class="col-sm-10">
							<?php echo $this->form->getInput('date'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('email') ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('email'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('voting_yes') ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('voting_yes'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('voting_no') ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('voting_no'); ?>
						</div>
					</div>
				</div>
			</div>

			<?php if($this->form->getGroup('customfields')) : ?>
				<div class="clearfix"></div>
				<div class="form-horizontal">
					<h2><?php echo JText::_('COM_COMMENT_CUSTOM_FIELDS'); ?></h2>
						<?php foreach($this->form->getGroup('customfields') as $custom) : ?>
							<?php
							    $this->form->setFieldAttribute($custom->fieldname, 'labelclass', 'col-sm-2 compojoom-control-label', 'customfields');
							?>
							<div class="form-group">
								<div class="col-sm2 compojoom-control-label">
									<?php echo $this->form->getLabel($custom->fieldname, 'customfields'); ?>
								</div>
								<div class="col-sm-10">
									<?php echo $this->form->getInput($custom->fieldname, 'customfields'); ?>
								</div>
							</div>
						<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token'); ?>
		</form>
	</div>
<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(CcommentHelperBasic::getFooterText());
