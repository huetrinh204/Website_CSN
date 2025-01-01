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

JHtml::_('jquery.framework');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
$document = JFactory::getDocument();
$input = JFactory::getApplication()->input;

JToolbarHelper::apply('settings.apply');
JToolbarHelper::save('settings.save');
JToolbarHelper::cancel('settings.cancel');

JHtml::script('media/com_comment/backend/js/settings.js');

$document->addScriptDeclaration(
    "(function($) {
			$(document).ready(function(){
				ccommentSettings('#jform_template_template', '" . $this->item->component . "')
			});
		})(jQuery);"
);

if (version_compare(JVERSION, '4.0', 'lt')) {
	CompojoomHtmlBehavior::bootstrap(false, true);
}
echo CompojoomHtmlCtemplate::getHead(CcommentHelperMenu::getMenu(), 'settings', '', '');
?>
<div class="box-info full">

    <h2>Comment configuration for <?php echo $input->getCmd('component'); ?></h2>
    <form action='index.php' method='POST' name='adminForm' id="adminForm" class="form-validate form-horizontal">

        <div class="additional-btn">
            <label class="ccomment-note-label"><?php echo JText::_('COM_COMMENT_NOTE_LABEL'); ?>:</label>
            <input type="text" class="input-xlarge" name="note"
                   placeholder="<?php echo JText::_('COM_COMMENT_NOTE_PLACEHOLDER'); ?>"
                   value="<?php echo $this->item->note; ?>" style="margin-bottom: 9px;"/>
        </div>

        <?php if (version_compare(JVERSION, '4.0', 'lt')): ?>

            <ul class="nav nav-tabs nav-justified">
                <li class="active">
                    <a data-toggle="tab" href="#general">
                        <?php echo JText::_('TAB_GENERAL_PAGE'); ?></a></li>
                <li><a data-toggle="tab" href="#security"><?php echo JText::_('TAB_SECURITY'); ?></a></li>
                <li><a data-toggle="tab" href="#layout"><?php echo JText::_('TAB_LAYOUT'); ?></a></li>
                <li><a data-toggle="tab" href="#template"><?php echo JText::_('COM_COMMENT_TAB_TEMPLATE'); ?></a>
                </li>
                <li><a data-toggle="tab"
                       href="#integrations"><?php echo JText::_('COM_COMMENT_TAB_INTEGRATIONS'); ?></a>
                </li>
            </ul>
            <div class="tab-content">
                <?php $tabs = array('general', 'security', 'layout', 'template', 'integrations'); ?>
                <?php foreach ($tabs as $key => $value) : ?>
                    <div id="<?php echo $value; ?>" class="tab-pane <?php echo $key == 0 ? 'active' : ''; ?>">
                        <?php if (!CCOMMENT_PRO) : ?>
                            <span class="ccomment-pro">
							* <?php echo JText::sprintf('COM_COMMENT_PRO_NOTICE', 'https://compojoom.com/joomla-extensions/compojoomcomment'); ?>
						</span>
                        <?php endif; ?>
                        <?php require_once($value . '.php'); ?>
                    </div>
                <?php endforeach; ?>
            </div>


        <?php else: ?>

            <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>


            <?php $tabs = array('general' => "TAB_GENERAL_PAGE", 'security' => "TAB_SECURITY", 'layout' => "TAB_LAYOUT",
                'template' => "COM_COMMENT_TAB_TEMPLATE", 'integrations' => "COM_COMMENT_TAB_INTEGRATIONS"); ?>
            <?php foreach ($tabs as $key => $value) : ?>
                <?php echo HTMLHelper::_('uitab.addTab', 'myTab', $key, Text::_($value)); ?>
                <div>
                    <?php if (!CCOMMENT_PRO) : ?>
                        <span class="ccomment-pro">
                                * <?php echo JText::sprintf('COM_COMMENT_PRO_NOTICE', 'https://compojoom.com/joomla-extensions/compojoomcomment'); ?>
                            </span>
                    <?php endif; ?>
                    <?php require_once($key . '.php'); ?>
                </div>
                <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <?php endforeach; ?>


            <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

        <?php endif; ?>


        <input type="hidden" name="id" value="<?php echo $this->item->id > 0 ? $this->item->id : ''; ?>"/>
        <input type="hidden" name="component" value="<?php echo $this->item->component; ?>"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="option" value="com_comment"/>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(CcommentHelperBasic::getFooterText());
?>

<style>
    .compojoom-bootstrap .col-sm-1, .compojoom-bootstrap .col-sm-2, .compojoom-bootstrap .col-sm-3, .compojoom-bootstrap .col-sm-4, .compojoom-bootstrap .col-sm-5, .compojoom-bootstrap .col-sm-6, .compojoom-bootstrap .col-sm-7, .compojoom-bootstrap .col-sm-8, .compojoom-bootstrap .col-sm-9, .compojoom-bootstrap .col-sm-10, .compojoom-bootstrap .col-sm-11, .compojoom-bootstrap .col-sm-12 {
        float: left;
    }

    .compojoom-bootstrap .form-horizontal .form-group {
        padding: 8px;
    }

    .compojoom-bootstrap .form-horizontal .form-group {
        margin-right: -15px;
        margin-left: -15px;
    }

    .compojoom-bootstrap .form-group {
        margin-bottom: 15px;
    }

    .compojoom-bootstrap * {
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }

    .compojoom-bootstrap .col-xs-1, .compojoom-bootstrap .col-sm-1, .compojoom-bootstrap .col-md-1, .compojoom-bootstrap .col-lg-1, .compojoom-bootstrap .col-xs-2, .compojoom-bootstrap .col-sm-2, .compojoom-bootstrap .col-md-2, .compojoom-bootstrap .col-lg-2, .compojoom-bootstrap .col-xs-3, .compojoom-bootstrap .col-sm-3, .compojoom-bootstrap .col-md-3, .compojoom-bootstrap .col-lg-3, .compojoom-bootstrap .col-xs-4, .compojoom-bootstrap .col-sm-4, .compojoom-bootstrap .col-md-4, .compojoom-bootstrap .col-lg-4, .compojoom-bootstrap .col-xs-5, .compojoom-bootstrap .col-sm-5, .compojoom-bootstrap .col-md-5, .compojoom-bootstrap .col-lg-5, .compojoom-bootstrap .col-xs-6, .compojoom-bootstrap .col-sm-6, .compojoom-bootstrap .col-md-6, .compojoom-bootstrap .col-lg-6, .compojoom-bootstrap .col-xs-7, .compojoom-bootstrap .col-sm-7, .compojoom-bootstrap .col-md-7, .compojoom-bootstrap .col-lg-7, .compojoom-bootstrap .col-xs-8, .compojoom-bootstrap .col-sm-8, .compojoom-bootstrap .col-md-8, .compojoom-bootstrap .col-lg-8, .compojoom-bootstrap .col-xs-9, .compojoom-bootstrap .col-sm-9, .compojoom-bootstrap .col-md-9, .compojoom-bootstrap .col-lg-9, .compojoom-bootstrap .col-xs-10, .compojoom-bootstrap .col-sm-10, .compojoom-bootstrap .col-md-10, .compojoom-bootstrap .col-lg-10, .compojoom-bootstrap .col-xs-11, .compojoom-bootstrap .col-sm-11, .compojoom-bootstrap .col-md-11, .compojoom-bootstrap .col-lg-11, .compojoom-bootstrap .col-xs-12, .compojoom-bootstrap .col-sm-12, .compojoom-bootstrap .col-md-12, .compojoom-bootstrap .col-lg-12 {
        position: relative;
        min-height: 1px;
        padding-right: 15px;
        padding-left: 15px;
    }

    .compojoom-bootstrap .clearfix::after, .compojoom-bootstrap .dl-horizontal dd::after, .compojoom-bootstrap .container::after, .compojoom-bootstrap .container-fluid::after, .compojoom-bootstrap .row::after, .compojoom-bootstrap .form-horizontal .form-group::after, .compojoom-bootstrap .btn-toolbar::after, .compojoom-bootstrap .btn-group-vertical > .btn-group::after, .compojoom-bootstrap .nav::after, .compojoom-bootstrap .navbar::after, .compojoom-bootstrap .navbar-header::after, .compojoom-bootstrap .navbar-collapse::after, .compojoom-bootstrap .pager::after, .compojoom-bootstrap .panel-body::after, .compojoom-bootstrap .modal-header::after, .compojoom-bootstrap .modal-footer::after {
        clear: both;
    }

    .compojoom-bootstrap .clearfix::before, .compojoom-bootstrap .clearfix::after, .compojoom-bootstrap .dl-horizontal dd::before, .compojoom-bootstrap .dl-horizontal dd::after, .compojoom-bootstrap .container::before, .compojoom-bootstrap .container::after, .compojoom-bootstrap .container-fluid::before, .compojoom-bootstrap .container-fluid::after, .compojoom-bootstrap .row::before, .compojoom-bootstrap .row::after, .compojoom-bootstrap .form-horizontal .form-group::before, .compojoom-bootstrap .form-horizontal .form-group::after, .compojoom-bootstrap .btn-toolbar::before, .compojoom-bootstrap .btn-toolbar::after, .compojoom-bootstrap .btn-group-vertical > .btn-group::before, .compojoom-bootstrap .btn-group-vertical > .btn-group::after, .compojoom-bootstrap .nav::before, .compojoom-bootstrap .nav::after, .compojoom-bootstrap .navbar::before, .compojoom-bootstrap .navbar::after, .compojoom-bootstrap .navbar-header::before, .compojoom-bootstrap .navbar-header::after, .compojoom-bootstrap .navbar-collapse::before, .compojoom-bootstrap .navbar-collapse::after, .compojoom-bootstrap .pager::before, .compojoom-bootstrap .pager::after, .compojoom-bootstrap .panel-body::before, .compojoom-bootstrap .panel-body::after, .compojoom-bootstrap .modal-header::before, .compojoom-bootstrap .modal-header::after, .compojoom-bootstrap .modal-footer::before, .compojoom-bootstrap .modal-footer::after {

        display: table;
        content: " ";
    }

<?php if (!version_compare(JVERSION, '4.0', 'lt')): ?>
    .btn-group .btn-outline-secondary {
        background: transparent;
        color: #000000;

    }
    .compojoom-bootstrap .btn-group > .btn.active {
        border-color: #cccccc;
    }
<?php endif; ?>
</style>
