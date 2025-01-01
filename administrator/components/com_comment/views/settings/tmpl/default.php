<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       11.04.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

JToolBarHelper::addNew('settings.choose');
JToolbarHelper::deleteList('COM_COMMENT_SETTING_DELETE_CONFIRM', 'settings.remove');
JToolbarHelper::preferences('com_comment');
if (version_compare(JVERSION, '4.0', 'lt')) {
	CompojoomHtmlBehavior::bootstrap(false, true);
}
echo CompojoomHtmlCtemplate::getHead(CcommentHelperMenu::getMenu(), 'settings', 'COM_COMMENT_INTEGRATION_SETTINGS', '');
?>

	<form action="" method="post" name="adminForm" id="adminForm">
		<div class="box-info full">
		<h2><?php echo $this->pagination->getResultsCounter(); ?></h2>

		<div class="additional-btn">
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>



			<table class="table table-hover table-striped">
				<thead>
				<tr>
					<th class="title">
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
					</th>
					<th class="title" width="10%"><?php echo JText::_('COM_COMMENT_COMPONENT_FIELD_LABEL'); ?></th>
					<th class="title"><?php echo JText::_('COM_COMMENT_NOTE'); ?></th>
				</tr>
				</thead>
				<?php
				for ($i = 0, $n = count($this->rows); $i < $n; $i++)
				{
					$row = $this->rows[$i];
					$checked = JHtml::_('grid.id', $i, $row->id);
					$link = JRoute::_('index.php?option=com_comment&task=settings.edit&component=' . $row->component);
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td width="1%">
							<?php echo $checked ?>
						</td>
						<td>
							<a href="<?php echo $link; ?>">
								<b><?php echo $row->component; ?></b>
							</a>
						</td>
						<td>
							<?php echo $row->note; ?>
						</td>
					</tr>
				<?php
				}
				?>
			</table>
		</div>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="option" value="com_comment"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="controller" value="settings"/>
		<?php echo JHtml::_('form.token'); ?>

	</form>

<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(CcommentHelperBasic::getFooterText());
