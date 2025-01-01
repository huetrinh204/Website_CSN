/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2023 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

((Joomla, document) => {

    // top namespace
    window.JFilters = window.JFilters || {};

    window.parent.applyFilters = function (client) {
        if (typeof JFilters.setUrlFromSelected == 'function') {
            JFilters.setUrlFromSelected(client);
        }
    };

    /**
     * Adds the generated url from the selected filters to an input
     *
     * @constructor
     * @param uri
     * @param inputElement
     */
    JFilters.addSelectedToInput = (uri, inputElement) => {

        if (inputElement && uri) {
            inputElement.value = uri;
        }
    }

    /**
     * Script that inserts the link in the editor.
     * After finishing closes the modal.
     */
    JFilters.addSelectedToEditor = (uri, label) => {

        if (!Joomla.getOptions('xtd-menus')) {
            // Something went wrong!
            window.parent.Joomla.Modal.getCurrent().close();
            throw new Error('core.js was not properly initialised');
        }

        const baseUrl = document.getElementById('jf_base_url').value;
        const editor = Joomla.getOptions('xtd-menus').editor;

        uri = baseUrl + '&amp;' + uri;

        // Insert the link in the editor
        if (window.parent.Joomla.editors.instances[editor].getSelection()) {
            window.parent.Joomla.editors.instances[editor].replaceSelection(`<a href="${uri}">${window.parent.Joomla.editors.instances[editor].getSelection()}</a>`);
        }else {
            if (!label) {
                label = prompt(Joomla.Text._('COM_JFILTERS_SET_ANCHOR_TEXT'));
            }
            if (label) {
                const tag = `<a href="${uri}">${label}</a>`;
                window.parent.Joomla.editors.instances[editor].replaceSelection(tag);
            }
        }
    };

    JFilters.setUrlFromSelected = (client) => {
        const selects = [].slice.call(document.querySelectorAll('.filter__options-select'));
        let url = '';
        let label = '';

        selects.forEach(select => {
            const selectedOptions = select.selectedOptions;
            const selectedLabel = [];

            for (const option of selectedOptions) {
                url += option.value;
                selectedLabel.push(option.text);
            }

            // Set label only when there is 1 selection
            if (!label && selectedLabel.length == 1) {
                label = selectedLabel[0];
            }
        });

        // We use the itemId only in editor
        let itemId = document.querySelector('#additional_Itemid') ? document.querySelector('#additional_Itemid').value : false;

        if (url) {
            // Add the itemId to the url
            if (itemId) {
                url += '&amp;Itemid=' + itemId;
            }

            // Remove dashes at the start of nested elements
            label = label.replace(/^[- ]+/, '');

            // Remove the counter at the end
            label = label.replace(/\s\(\d+\){1}$/, '');

            if (!client || client== 'editor') {
                JFilters.addSelectedToEditor(url, label);
            } else {
                // Get the parent input
                console.log(window.parent.Joomla.Modal.getCurrent());
                const inputEl = window.parent.Joomla.Modal.getCurrent().previousSibling.querySelector('.jfilters_filters_selected');
                JFilters.addSelectedToInput(url, inputEl);
            }
        }

        // Close the modal
        if (window.parent.Joomla && window.parent.Joomla.Modal) {
            window.parent.Joomla.Modal.getCurrent().close();
        }
    };
})(Joomla, document);