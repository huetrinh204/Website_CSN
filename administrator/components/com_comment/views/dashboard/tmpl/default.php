<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       07.10.14
 *
 * @copyright  Copyright (C) 2008 - 2014 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */


defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;

$extensionName = 'CComment';
JToolBarHelper::preferences('com_comment');

JHtml::script('https://www.gstatic.com/charts/loader.js');

echo CompojoomHtmlCtemplate::getHead(CcommentHelperMenu::getMenu(), 'cpanel', 'COM_COMMENT_DASHBOARD', '');
if (version_compare(JVERSION, '4.0', 'lt')) {

	CompojoomHtmlBehavior::bootstrap(false, true);
}
?>

<?php if (CCOMMENT_PRO && (version_compare(JVERSION, '2.5.19', 'lt') || (version_compare(JVERSION, '3.0.0', 'gt') && version_compare(JVERSION, '3.2.1', 'lt')))):?>
	<div class="alert alert-error">
		<?php echo JText::sprintf('LIB_COMPOJOOM_ERR_OLDJOOMLANOUPDATES', $extensionName); ?>
	</div>
<?php elseif (CCOMMENT_PRO && version_compare(JVERSION, '2.5.999', 'lt') && !$this->updatePlugin): ?>
	<div class="alert alert-warning">
		<?php echo JText::sprintf('LIB_COMPOJOOM_ERR_NOPLUGINNOUPDATES', $extensionName, $extensionName); ?>
	</div>
<?php endif; ?>

<?php if($this->needsdlid): ?>
	<div class="alert alert-danger">
		<?php echo JText::sprintf('LIB_COMPOJOOM_DASHBOARD_NEEDSDLID', 'CComment Professional', 'https://compojoom.com/support/documentation/ccomment/ch02'); ?>
	</div>
<?php elseif ($this->needscoredlidwarning): ?>
	<div class="alert alert-danger">
		<?php echo JText::sprintf('LIB_COMPOJOOM_DASHBOARD_NEEDSUPGRADE', 'CComment Professional', 'CComment Professional'); ?>
	</div>
<?php endif; ?>

<div id="updateNotice"></div>
<div id="jedNotice"></div>

<div class="row">
	<div class="col-sm-6">
		<div class="box-info">
			<h2><?php echo JText::_('COM_COMMENT_USER_ENGAGEMENT'); ?></h2>

			<div class="row-fluid" id="activity-chart">
				<?php if (count($this->statsArray) == 1) : ?>
					<span class="ccomment-no-stats">
						<?php echo JText::_('COM_COMMENT_NO_DATA_FOR_LAST_30_DAYS'); ?>
						</span>
				<?php endif; ?>
			</div>
		</div>
		<div class="box-info full">
			<h2><?php echo JText::sprintf('COM_COMMENT_LATEST_X_COMMENTS', 5); ?></h2>
			<?php if ($this->latest) : ?>
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
						<tr>
							<th><?php echo JText::_('COM_COMMENT_COMMENT_TITLE_TH'); ?></th>
							<th><?php echo JText::_('COM_COMMENT_DATE'); ?></th>
							<th><?php echo JText::_('COM_COMMENT_PUBLISH'); ?></th>
							<th><?php echo JText::_('COM_COMMENT_ACTION'); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($this->latest as $item): ?>
							<tr>
								<td>
									<?php echo StringHelper::substr($this->escape($item->comment), 0, 140); ?>
								</td>
								<td>
									<?php echo $item->date; ?>
								</td>
								<td>
									<?php echo JText::_(($item->published ? 'JYES' : 'JNO')); ?>
								</td>
								<td>
									<a href="<?php echo JRoute::_('index.php?option=com_comment&task=comment.edit&id=' . $item->id); ?>">Edit</a>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else: ?>
				<span class="ccomment-no-stats"><?php echo JText::_('COM_COMMENT_NO_COMMENTS'); ?></span>
			<?php endif; ?>
		</div>
	</div>
	<div class="col-sm-6">
		<div class=" box-info full">
            <?php if (version_compare(JVERSION, '4.0', 'lt')): ?>
			<ul class="nav nav-tabs nav-justified">
				<li class="active">
					<a data-toggle="tab" href="#rss">
						<?php echo JText::_('LIB_COMPOJOOM_LATEST_NEWS'); ?>
					</a>
				</li>
				<li>
					<a data-toggle="tab" href="#version">
						<?php echo JText::_('LIB_COMPOJOOM_VERSION_INFO'); ?>
					</a>
				</li>
			</ul>
			<div class="tab-content">
				<div id="rss" class="tab-pane active">
					<?php echo CompojoomHtmlFeed::renderFeed('https://compojoom.com/blog/tags/listings/ccomment?format=feed&amp;type=rss'); ?>
				</div>
				<div id="version" class="tab-pane">
					<?php echo $this->loadTemplate('version'); ?>
				</div>
			</div>

            <?php else: ?>

            <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'rss')); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'rss', Text::_('LIB_COMPOJOOM_LATEST_NEWS')); ?>
                <?php echo CompojoomHtmlFeed::renderFeed('https://compojoom.com/blog/tags/listings/ccomment?format=feed&amp;type=rss'); ?>

            <?php echo HTMLHelper::_('uitab.endTab'); ?>


            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'version', Text::_('LIB_COMPOJOOM_VERSION_INFO')); ?>
                <?php echo $this->loadTemplate('version'); ?>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

            <?php endif; ?>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<div class="box-info">
			<p>
				<?php echo JText::sprintf('COM_COMMENT_LANGUAGE_PACK', 'https://compojoom.com/downloads/languages-cool-geil/ccomment'); ?>
			</p>
			<strong>
				CComment <?php echo CCOMMENT_PRO ? 'PRO' : 'Core'; ?>
				<?php echo ccommentHelperComponents::getComponentVersion('com_comment')->get('version'); ?></strong>
			<br>
			<span style="font-size: x-small">
				Copyright &copy;2008&ndash;<?php echo date('Y'); ?> Daniel Dimitrov / compojoom.com
			</span>
			<br>

			<?php if (CCOMMENT_PRO) : ?>
				<strong>
					If you use CComment PRO, please post a rating and a review at the
					<a href="http://extensions.joomla.org/extensions/contacts-and-feedback/articles-comments/12259"
					   target="_blank">Joomla! Extensions Directory</a>.
				</strong>
			<?php endif; ?>
			<br>


			<div>
				<?php echo CompojoomHtmlTemplates::renderSocialMediaInfo(); ?>
			</div>

			<span style="font-size: x-small">
				CComment is Free software released under the
				<a href="http://www.gnu.org/licenses/gpl.html">GNU General Public License,</a>
				version 2 of the license or &ndash;at your option&ndash; any later version
				published by the Free Software Foundation.
			</span>

		</div>
	</div>
</div>


<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(CcommentHelperBasic::getFooterText());
?>

<?php if (count($this->statsArray) > 1) : ?>
	<script type="text/javascript">
          google.charts.load('current', {packages: ['corechart']});
          google.charts.setOnLoadCallback(drawChart);

		function drawChart() {
			var data = google.visualization.arrayToDataTable(
				<?php echo json_encode($this->statsArray); ?>
			);

			var options = {
				vAxis: {title: '<?php echo JText::_('COM_COMMENT_COMMENTS'); ?>', titleTextStyle: {color: 'red'}}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('activity-chart'));
			chart.draw(data, options);
		}

	</script>
<?php endif; ?>

<script type="text/javascript">
	(function($) {
		$(document).ready(function(){
			$.ajax('index.php?option=com_comment&task=update.updateinfo&tmpl=component', {
				success: function(msg, textStatus, jqXHR)
				{
					// Get rid of junk before and after data
					var match = msg.match(/###([\s\S]*?)###/);
					data = match[1];

					if (data.length)
					{
						$('#updateNotice').html(data);
					}
				}
			})
		});
		$.ajax('index.php?option=com_comment&task=jed.reviewed&tmpl=component&<?php echo JSession::getFormToken(); ?>=1', {
			success: function(msg, textStatus, jqXHR)
			{
				// Get rid of junk before and after data
				var match = msg.match(/###([\s\S]*?)###/);
				data = match[1];

				if (data.length)
				{
					$('#jedNotice').html(data);
				}
			}
		})
	})(jQuery);
</script>
