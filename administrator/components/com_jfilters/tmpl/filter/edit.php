<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_jfilters.jfilters');

Text::script('COM_JFILTERS_FIELD_MAX_PATH_NESTING_WARNING', true);
$app = Factory::getApplication();
$input = $app->input;

//required to use the ui functions such as the tabs
$this->useCoreUI = true;

/** @var \Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface $filterItem */
$filterItem = $this->filterItem;
?>

<form action="<?php echo Route::_('index.php?option=com_jfilters&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <div class="row title-alias form-vertical mb-3">
        <div class="col-12 col-md-6">
            <?php echo $this->form->renderField('label'); ?>
        </div>
        <div class="col-12 col-md-6">
            <?php echo $this->form->renderField('alias'); ?>
        </div>
    </div>

    <div>
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_JFILTERS_EDIT_FILTER')); ?>
        <div class="row">
            <div class="col-md-9">
                <fieldset class="adminform">
                    <?php echo $this->form->renderField('display'); ?>
                    <?php echo $this->form->renderField('date_format'); ?>
                    <?php echo $this->form->renderField('root'); ?>
                    <?php echo $this->form->renderField('config_type'); ?>
                    <?php echo $this->form->renderField('name'); ?>
                    <?php echo $this->form->renderField('context_alias'); ?>
                </fieldset>
            </div>
            <div class="col-md-3">
                <div class="card card-light">
                    <div class="card-body">
                        <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
                    </div>
                </div>
            </div>
        </div>

        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'basic-attribs', Text::_('COM_JFILTERS_BASIC_ATTRIBS_FIELDSET_LABEL')); ?>
        <fieldset id="fieldset-basic-attribs" class="options-form">
            <legend><?php echo Text::_('COM_JFILTERS_BASIC_ATTRIBS_FIELDSET_LABEL'); ?></legend>
            <div class="column-count-md-2 column-count-lg-3">
                <?php echo $this->form->renderFieldset('basic-attribs'); ?>
            </div>
        </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        if ($filterItem->getConfig()->getValue()->getIsTree()) :?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'tree-attribs', Text::_('COM_JFILTERS_TREE_ATTRIBS_FIELDSET_LABEL')); ?>
            <fieldset id="fieldset-tree-attribs" class="options-form">
                <legend><?php echo Text::_('COM_JFILTERS_TREE_ATTRIBS_FIELDSET_LABEL'); ?></legend>
                <div class="column-count-md-2 column-count-lg-3">
                    <?php echo $this->form->renderFieldset('tree-attribs'); ?>
                </div>
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif;?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'seo-attribs', Text::_('COM_JFILTERS_SEO_ATTRIBS_FIELDSET_LABEL')); ?>
        <fieldset id="fieldset-seo-attribs" class="options-form">
            <legend><?php echo Text::_('COM_JFILTERS_SEO_ATTRIBS_FIELDSET_LABEL'); ?></legend>
            <div class="column-count-md-2 column-count-lg-3">
                <?php echo $this->form->renderFieldset('seo-attribs'); ?>
            </div>
        </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        if (!$filterItem->getRoot()) :?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'advanced-attribs', Text::_('COM_JFILTERS_ADVANCED_FIELDSET_LABEL')); ?>
            <fieldset id="fieldset-advanced-attribs" class="options-form">
                <legend><?php echo Text::_('COM_JFILTERS_ADVANCED_FIELDSET_LABEL'); ?></legend>
                <div class="column-count-md-2 column-count-lg-3">
                    <?php echo $this->form->renderFieldset('advanced-attribs'); ?>
                </div>
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif;?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

        <?php $hidden_fields = $this->form->getInput('id'); ?>
        <div class="hidden"><?php echo $hidden_fields; ?></div>
        <input type="hidden" name="task" value="">
        <input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
