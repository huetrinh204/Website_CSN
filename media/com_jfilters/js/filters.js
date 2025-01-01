/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2023 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */
Joomla = window.Joomla || {};

(() => {

    document.addEventListener('DOMContentLoaded', () => {
        Joomla.submitbutton = pressbutton => {
            // TODO replace with joomla-alert
            if (pressbutton === 'filters.purge' && !window.confirm(Joomla.Text._('COM_JFILTERS_FILTERS_CONFIRM_PURGE_PROMPT'))) {
                return false;
            } // TODO replace with joomla-alert

            Joomla.submitform(pressbutton);
            return true;
        };
    });
})();