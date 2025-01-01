<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Site\View\Module;

\defined('_JEXEC') or die();

use Bluecoder\Component\Jfilters\Site\Helper\ModuleHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    /**
     * @var \stdClass
     */
    protected $module;

    /**
     * @param   null  $tpl
     *
     * @throws \Exception
     * @since 1.5.0
     */
    public function display($tpl = null)
    {
        $jinput = Factory::getApplication()->input;
        $module_id = $jinput->get('module_id', '', 'int');
        $this->module = ModuleHelper::getModule($module_id);

        parent::display($tpl);
    }
}