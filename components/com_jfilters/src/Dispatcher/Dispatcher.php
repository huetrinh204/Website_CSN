<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Site\Dispatcher;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_jfilters
 * Dispatcher is used to check ACL and dispatch to the proper controller
 *
 * @since  1.10.2
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Dispatch a controller task. Redirecting the user if appropriate.
     *
     * @return  void
     *
     * @since  1.10.2
     */
    public function dispatch()
    {
        if ($this->input->get('view') === 'filters' &&
            (
                !$this->app->getIdentity()->authorise('core.edit', 'com_jfilters') &&
                !$this->app->getIdentity()->authorise('core.edit.own', 'com_jfilters')
            )
        ) {
                $this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');

                return;
        }

        parent::dispatch();
    }
}