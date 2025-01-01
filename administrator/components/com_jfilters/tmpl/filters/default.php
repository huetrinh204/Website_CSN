<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Field\ConfigtypeField;
use Bluecoder\Component\Jfilters\Administrator\Field\DisplaytypesField;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextCollection;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

HTMLHelper::_('behavior.multiselect');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_jfilters.jfilters');
$wa->useScript('com_jfilters.filters');

Text::script('COM_JFILTERS_FILTERS_CONFIRM_PURGE_PROMPT');

$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder && !empty($this->items))
{
    $saveOrderingUrl = 'index.php?option=com_jfilters&task=filters.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_jfilters'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <?php if (!empty($this->sidebar)) : ?>
            <div id="j-sidebar-container" class="col-md-2">
                <?php echo $this->sidebar; ?>
            </div>
        <?php endif; ?>
        <div class="<?php if (!empty($this->sidebar)) {echo 'col-md-10'; } else { echo 'col-md-12'; } ?>">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="fa fa-info-circle" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table" id="jfilters-list-filters">
                        <caption id="captionTable" class="visually-hidden">
                            <?php echo Text::_('COM_JFILTERS_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>

                                <th scope="col" style="width:1%" class="text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                                </th>

                                <th scope="col" style="min-width:85px" class="w-1 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'state', $listDirn, $listOrder); ?>
                                </th>

                                <th scope="col" style="min-width:160px">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'label', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="nowrap">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_JFILTERS_FIELD_NAME_LABEL', 'name', $listDirn, $listOrder); ?>
                                </th>

                                <th scope="col" class="nowrap">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_JFILTERS_FIELD_TYPE_LABEL', 'config_name', $listDirn, $listOrder); ?>
                                </th>

                                <th scope="col" class="nowrap">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_JFILTERS_FIELD_CONTEXT_LABEL', 'context', $listDirn, $listOrder); ?>
                                </th>

                                <th scope="col" class="nowrap">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_JFILTERS_FIELD_DISPLAY_LABEL', 'display', $listDirn, $listOrder); ?>
                                </th>

                                <th scope="col" style="width:10%" class="d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access', $listDirn, $listOrder); ?>
                                </th>

                                <th scope="col" class="nowrap">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_JFILTERS_FIELD_ROOT_LABEL', 'root', $listDirn, $listOrder); ?>
                                </th>

                                <?php if (Multilanguage::isEnabled()) : ?>
                                    <th scope="col" style="width:10%" class="d-none d-md-table-cell">
                                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                                    </th>
                                <?php endif; ?>

                                <th scope="col" style="width:5%" class=" d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
                        <?php
                        $n = count($this->items);
                        foreach ($this->items as $i => $item) :
                            $canEdit    = $user->authorise('core.edit',       'com_jfilters.filter.' . $item->id);
                            $canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                            $canChange  = $user->authorise('core.edit.state', 'com_jfilters.filter.' . $item->id) && $canCheckin;
                            ?>
                            <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->context?>">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                </td>

                                <td class="text-center d-none d-md-table-cell">
                                    <?php $iconClass = ''; ?>
                                    <?php if (!$canChange) : ?>
                                        <?php $iconClass = ' inactive'; ?>
                                    <?php elseif (!$saveOrder) : ?>
                                        <?php $iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::tooltipText('JORDERINGDISABLED'); ?>
                                    <?php endif; ?>
                                    <span class="sortable-handler<?php echo $iconClass; ?>">
											<span class="icon-menu" aria-hidden="true"></span>
                                    </span>
                                    <?php if ($canChange && $saveOrder) : ?>
                                        <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>">
                                    <?php endif; ?>
                                </td>

                                <td class="text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'filters.', $canChange, 'cb', null, null); ?>
                                </td>

                                <th scope="row" class="has-context">
                                    <div class="break-word">
                                        <?php if ($item->checked_out) : ?>
                                            <?php
                                            if ($item->checked_out_time === null) {
                                                // see: https://github.com/breakdesigns/com_jfilters/issues/48
                                                $item->checked_out_time = 'now';
                                            }
                                            echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'filters.', $canCheckin); ?>
                                        <?php endif; ?>
                                        <?php if ($canEdit) : ?>
                                            <a href="<?php echo Route::_('index.php?option=com_jfilters&task=filter.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->label)); ?>">
                                                <?php echo $this->escape($item->label); ?></a>
                                        <?php else : ?>
                                            <?php echo $this->escape($item->label); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small break-word">
                                        <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                    </div>
                                </th>

                                <td class="d-none d-md-table-cell">
                                    <?php echo $this->escape($item->name);?>
                                </td>

                                <td class="d-none d-md-table-cell">
                                    <?php echo Text::_(ConfigtypeField::createLabel($item->config_name)), $item->attributes->get('type') ? ' (' . $item->attributes->get('type') . ')' : '';?>
                                </td>

                                <td class="d-none d-md-table-cell">
                                    <?php
                                    /** @var  ContextCollection $contextCollection */
                                    $contextCollection = $this->contextCollection;
                                    $context = $contextCollection->getByNameAttribute($item->context);
                                    echo $context ? Text::_($context->getAlias()) : $item->context; ?>
                                </td>

                                <td class="d-none d-md-table-cell">
                                    <?php echo DisplaytypesField::getDisplayTypeName($item->display); ?>
                                </td>

                                <td class="small d-none d-md-table-cell text-center">
                                    <?php echo $this->escape($item->access_level); ?>
                                </td>

                                <td class="d-none d-md-table-cell">
                                    <div class="tbody-icon">
                                        <span class="isroot <?php echo $item->root ? ' icon-home' : '';?>" aria-hidden="true"></span>
                                    </div>
                                </td>

                                <?php if (Multilanguage::isEnabled()) : ?>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
                                    </td>
                                <?php endif; ?>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $item->id; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php // load the pagination. ?>
                    <?php echo $this->pagination->getListFooter(); ?>

                    <?php // Load the batch processing form. ?>

                <?php endif; ?>
                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>

<?php @include_once __DIR__ . '/../footer.php';