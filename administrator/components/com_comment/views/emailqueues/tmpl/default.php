<?php
/**
 * @package    CComment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       07.10.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
JtoolbarHelper::publish('comments.publish');
JtoolbarHelper::unpublish('comments.unpublish');
JtoolbarHelper::editList('comment.edit');
JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'emailqueues.delete');
JToolbarHelper::custom('emailqueues.deleteAll', 'delete', 'COM_COMMENT_DELETE_ITEMS_ALL', 'Delete All', false);
JToolbarHelper::custom('emailqueues.sendMail','envelope','COM_COMMENT_SEND_MAIL', 'Send mail', false);

$listOrder         = $this->escape($this->state->get('list.ordering'));
$listDirn          = $this->escape($this->state->get('list.direction'));
$selectedComponent = $this->escape($this->state->get('filter.component'));


echo CompojoomHtmlCtemplate::getHead(CcommentHelperMenu::getMenu(), 'queue', 'Email queue', '');
?>
	<form action="" method="post" name="adminForm" id="adminForm">
		<div class="box-info full">
			<h2><?php echo $this->pagination->getResultsCounter(); ?></h2>

			<div class="additional-btn">
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="col-md-4">
				<div class="input-group">
					<input type="text" name="filter_search" id="filter_search"
						   placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>"
						   value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
						   class="form-control"
						   onchange="document.adminForm.submit();"/>
					<span class="input-group-btn">
						<button onclick="this.form.submit();" class="btn btn-default" type="submit">
							<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
						</button>
						<button class="btn btn-default" type="button" onclick="document.getElementById('filter_search').value='';
												this.form.getElementById('filter_published').value='*';
												this.form.getElementById('component').value='';
												this.form.submit();">
							<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
						</button>
					</span>
				</div>
			</div>
			<div class="table-responsive">
				<table class="table table-hover table-striped">
					<thead>
					<tr>
						<th width="2%" class="title">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'Mail from', 'name', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'Sent from', 'userid', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'Sent to', 'notify', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'Subject', 'subject', $listDirn, $listOrder); ?>
						</th>
						<th class="title" nowrap="nowrap" width="20%">
							<?php echo JHtml::_('grid.sort', 'COM_COMMENT_COMMENT_TITLE_TH', 'comment', $listDirn, $listOrder, 0, null, 'COM_COMMENT_COMMENT_TITLE_TH_DESC'); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'Type', 'type', $listDirn, $listOrder); ?>
						</th>
						<th class="title" nowrap="nowrap">
							<?php echo JHtml::_('grid.sort', 'Status', 'status', $listDirn, $listOrder, 0, null, 'COM_COMMENT_COMMENT_TITLE_TH_DESC'); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php
					for ($i = 0, $n = count($this->items); $i < $n; $i++) :
						$item = $this->items[$i];

						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td>
								<?php echo JHtml::_('grid.id', $i, $item->id);?>

							</td>
							<td align="center">
								<?php echo $item->mailfrom; ?>
							</td>
							<td align="center">
								<?php echo $item->fromname; ?>
							</td>
							<td align="center">
								<?php echo $item->recipient; ?>
							</td>
							<td>
								<?php echo $item->subject; ?>
							</td>
							<td>
								<?php echo $item->body; ?>
							</td>

							<td align="center">
								<?php echo $item->type; ?>
							</td>
							<td align="center">
								<?php if ($item->status) :?>
									<?php echo JText::_('COM_COMMENT_EMAIL_QUEUE_SENT') ?>
								<?php else :?>
									<?php echo JText::_('COM_COMMENT_EMAIL_QUEUE_NOT_SENT')?>
								<?php endif; ?>
							</td>
					<?php
					endfor;

					if (!count($this->items))
					:
						?>
						<tr>
							<td colspan="16">
								<?php echo JText::_('No items'); ?>
							</td>
						</tr>
					<?php
					endif;
					?>
					<tr>
						<td colspan="16">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="option" value="com_comment"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="controller" value="emailQueues"/>
		<input type="hidden" name="view" value="emailQueues"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>

<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(CcommentHelperBasic::getFooterText());

