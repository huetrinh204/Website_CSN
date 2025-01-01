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
use Joomla\String\StringHelper;

JtoolbarHelper::publish('comments.publish');
JtoolbarHelper::unpublish('comments.unpublish');
JtoolbarHelper::editList('comment.edit');
JToolbarHelper::deleteList('COM_COMMENT_DELETE_COMMENTS', 'comments.delete');

$listOrder         = $this->escape($this->state->get('list.ordering'));
$listDirn          = $this->escape($this->state->get('list.direction'));
$selectedComponent = $this->escape($this->state->get('filter.component'));

echo CompojoomHtmlCtemplate::getHead(CcommentHelperMenu::getMenu(), 'comments', 'COM_COMMENT_MANAGE_COMMENTS', '');
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

			<div class="col-md-8">
				<div class="form-inline">
					<div class="pull-right">
						<?php echo JHtml::_(
							'select.genericlist', $this->componentList, 'component', 'class="inputbox"
						onchange="submitform();"', 'value', 'text', $selectedComponent
						); ?>
						<select id="filter_published" name="filter_published" class="inputbox"
						        onchange="this.form.submit();">
							<?php echo JHtml::_(
								'select.options',
								JHtml::_('jgrid.publishedOptions', array('trash' => false, 'archived' => false)),
								'value', 'text', $this->state->get('filter.published'), true
							); ?>
						</select>
					</div>

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
							<?php echo JHtml::_('grid.sort', 'viewcom_writer', 'name', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'viewcom_userid', 'userid', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'viewcom_notify', 'notify', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'viewcom_date', 'date', $listDirn, $listOrder); ?>
						</th>
						<th class="title" nowrap="nowrap" width="20%">
							<?php echo JHtml::_('grid.sort', 'COM_COMMENT_COMMENT_TITLE_TH', 'comment', $listDirn, $listOrder, 0, null, 'COM_COMMENT_COMMENT_TITLE_TH_DESC'); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'viewcom_contentitem', 'contentid', $listDirn, $listOrder); ?>
						</th>
						<th class="title" nowrap="nowrap">
							<?php echo JHtml::_('grid.sort', 'COM_COMMENT_COMMENT_COMPONENT_TH', 'comment', $listDirn, $listOrder, 0, null, 'COM_COMMENT_COMMENT_TITLE_TH_DESC'); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'viewcom_published', 'published', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'viewcom_delete', 'delete', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'viewcom_ip', 'delete', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_COMMENT_VOTES') ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'viewcom_parentid', 'parentid', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'viewcom_importtable', 'importtable', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'viewcom_id', 'id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php
					for ($i = 0, $n = count($this->comments); $i < $n; $i++) :
						$comment = $this->comments[$i];

						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td>
								<?php echo $comment->checked; ?>
							</td>

							<td align="center">
								<a href="<?php echo $comment->link_edit; ?>"><?php echo $this->escape($comment->name); ?></a>
							</td>
							<td align="center">
								<?php echo $comment->userid; ?>
							</td>
							<td align="center">
								<?php echo $comment->notify; ?>
							</td>
							<td align="center">
								<?php echo $comment->date; ?>
							</td>
							<td>
								<?php echo $this->escape($comment->comment); ?>
							</td>
							<td align="center">
								<a href="<?php echo $comment->link ?>" target="_blank">
									<?php if (isset($this->titles[$comment->component][$comment->contentid])) : ?>
										<?php echo StringHelper::substr($this->titles[$comment->component][$comment->contentid]->title, 0, 25); ?>
										<?php if (StringHelper::strlen($this->titles[$comment->component][$comment->contentid]->title) > 40) : ?>
											...
										<?php endif; ?>
									<?php else: ?>
										<?php echo JText::_('COM_COMMENT_ITEM_NO_TITLE'); ?>
									<?php endif; ?>
								</a>
							</td>
							<td>
								<?php echo $comment->component; ?>
							</td>
							<td align="center">
				<span class="hasTip" title="<?php echo JText::_('NOTIFYPUBLISH'); ?>">
					<?php echo $comment->published ?>
				</span>
							</td>
							<td align="center">
								<?php echo $comment->delete; ?>
							</td>
							<td align="center"><?php echo $comment->ip; ?></td>
							<td align="center">
								<div class="pull-left">
									<?php echo JText::_('JYES') ?>: <?php echo $comment->voting_yes; ?><br/>
									<?php echo JText::_('JNO') ?>: <?php echo $comment->voting_no; ?><br/>
								</div>
								<div class="pull-right">
									<?php echo JText::_('COM_COMMENT_TOTAL') ?>
									:  <?php echo $comment->voting_yes - $comment->voting_no; ?>
								</div>
							</td>
							<td align="center"><?php echo $comment->parentid; ?></td>
							<td align="center"><?php echo $comment->importtable; ?></td>
							<td align="center"><?php echo $comment->id; ?></td>
						</tr>
					<?php
					endfor;

					if (!count($this->comments)) :
						?>
						<tr>
							<td colspan="16">
								<?php echo JText::_('No comments'); ?>
							</td>
						</tr>
					<?php endif;
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
		<input type="hidden" name="controller" value="comments"/>
		<input type="hidden" name="view" value="comments"/>
		<input type="hidden" name="confirm_notify" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</form>

<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(CcommentHelperBasic::getFooterText());
