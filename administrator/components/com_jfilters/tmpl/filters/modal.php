<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Administrator\Field\ConfigtypeField;
use Bluecoder\Component\Jfilters\Administrator\Model\Config\Context\Collection as ContextCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Collection as FilterCollection;
use Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\UriHandlerInterface;
use Bluecoder\Component\Jfilters\Administrator\ObjectManager;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$app = Factory::getApplication();

if ($app->isClient('site')) {
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

HTMLHelper::_('bootstrap.collapse');

/** @var \Bluecoder\Component\Jfilters\Administrator\View\Filters\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_jfilters.jfilters');
$wa->useScript('com_jfilters.filters');
$wa->useScript('com_jfilters.filters-modal');

Text::script('COM_JFILTERS_SET_ANCHOR_TEXT');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$editor    = $app->input->getCmd('editor', '');
$modalClient = explode('.', $app->input->get('client', ''));
$modalClient = reset($modalClient);
$urlBase = 'index.php?option=' . UriHandlerInterface::COMPONENT . '&amp;view=' . UriHandlerInterface::VIEW;

if ($modalClient == 'editor' && !empty($editor)) {
    // Load the xtd script only if the editor is set!
    $this->document->addScriptOptions('xtd-menus', ['editor' => $editor]);
}

/** @var FilterCollection $filtersCollection */
$filtersCollection = ObjectManager::getInstance()->getObject(FilterCollection::class);
// Load only published and listening state
$filtersCollection->addCondition('filter.state', [1, 2]);
?>
<form action="<?php echo Route::_('index.php?option=com_jfilters&view=filters&tmpl=component&layout=modal&editor=' . $editor . '&' . Session::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php if ($modalClient == 'editor') { ?>
                <div class="card bg-light pt-4 pb-3 ps-4 pe-4 mb-3">
                    <div class="col-lg-4">
                        <?php echo $this->filterForm->getField('Itemid', 'additional')->renderField(); ?>
                    </div>
                </div>
                <?php } ?>
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
                            <th scope="col" style="width:1%" class="text-center d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                            </th>

                            <th scope="col" style="min-width:85px" class="w-1 text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'state', $listDirn, $listOrder); ?>
                            </th>

                            <th scope="col" style="min-width:160px; width: 20%;">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'label', $listDirn, $listOrder); ?>
                            </th>

                            <th scope="col" class="nowrap">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_JFILTERS_FIELD_TYPE_LABEL', 'config_name', $listDirn, $listOrder); ?>
                            </th>

                            <th scope="col" class="nowrap">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_JFILTERS_FIELD_CONTEXT_LABEL', 'context', $listDirn, $listOrder); ?>
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
                        <tbody>
                        <?php
                        $n = count($this->items);
                        foreach ($this->items as $i => $item) :
                            /** @var \Bluecoder\Component\Jfilters\Administrator\Model\FilterInterface $filter */
                            $filter = $filtersCollection->getByAttribute('id', $item->id);
                            ?>
                            <tr class="row<?php echo $i % 2; ?> collapsible" data-bs-toggle="collapse" data-bs-target="#collapse<?= $item->id?>" role="button" aria-expanded="false" aria-controls="collapse<?= $item->id?>">

                                <td class="text-center d-none d-md-table-cell">
                                    <span class="sortable-handler inactive">
                                        <span class="icon-menu" aria-hidden="true"></span>
                                    </span>
                                </td>

                                <td class="text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'filters.', false, 'cb', null, null); ?>
                                </td>

                                <td class="has-context">
                                    <div class="break-word fw-bolder">
                                        <?php echo $this->escape($item->label); ?>
                                    </div>
                                    <div class="small break-word">
                                        <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                    </div>
                                    <div class="expand-icon">
                                        <span class="icon-chevron-down" aria-hidden="true"></span>
                                    </div>
                                </td>

                                <td class="d-none d-md-table-cell">
                                    <?php echo Text::_(ConfigtypeField::createLabel($item->config_name)), $item->attributes->get('type') ? ' (' . $item->attributes->get('type') . ')' : '';?>
                                </td>

                                <td class="d-none d-md-table-cell middle-cell">
                                    <?php
                                    /** @var  ContextCollection $contextCollection */
                                    $contextCollection = $this->contextCollection;
                                    $context = $contextCollection->getByNameAttribute($item->context);
                                    echo $context ? Text::_($context->getAlias()) : $item->context; ?>
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
                            <?php
                                if ($filter) {
                                    $filter->getOptions()->setUseOtherSelectionsAsConditions(false);
                                    // Expanded or collapsed. keep it expanded if it has selections.
                                    $collapseClass = $filter && $filter->getState() > 0 && $filter->getOptions()->getSelected() ? '.show' : '';
                                }

                                $this->filter = $filter;
                                ?>
                            <tr class="collapse<?=  $collapseClass;?>" id="collapse<?= $item->id?>">
                                <td></td>
                                <td></td>
                                <td colspan="4">
                                    <?php echo $this->loadTemplate('filter'); ?>
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
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
                <input type="hidden" name="client" value="<?= $modalClient;?>">
                <input type="hidden" name="jf_base_url" id="jf_base_url" value="<?= $urlBase; ?>" />
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>