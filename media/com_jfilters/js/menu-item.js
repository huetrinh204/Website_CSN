/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

document.addEventListener("DOMContentLoaded", function() {
    let subFormRowsCounter = 0;
    // We have to initialize the modals in the subform to work
    const sortingRulesEl = document.querySelector('joomla-field-subform[name="jform[params][sorting_rules]"]');
    if (sortingRulesEl) {
        sortingRulesEl.addEventListener('subform-row-add', (event) => {
            const rowElement = event.detail.row;
            const modalEl = rowElement.querySelector('.joomla-modal');

            /*
             * We need to change the modal id and its references on every row addition.
             * If not, all the modals will have the same ids
             */
            const modalId = modalEl.id + '-' + subFormRowsCounter++;
            modalEl.id = modalId;

            const modalOpenBtn = rowElement.querySelector('button[data-bs-toggle]');
            if (modalOpenBtn) {
                // Change the `data-bs-target` attribute
                modalOpenBtn.setAttribute('data-bs-target', '#'+modalId);
            }

            if (modalEl) {
                const options = {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                };
                Joomla.initialiseModal(modalEl, options);
            }
        });
    }

    const contextEl = document.getElementById('jform_params_contextType');

    // Clear set filters when context changes
    contextEl.addEventListener("change" , () => {
        clearFiltersFromModalURL();
        updateContextInModalURL();

        //clear the inputs
        let inputs = document.querySelectorAll(".jfilters_filters_selected");
        inputs.forEach((input) => {
            input.value = "";
        })
    })

    const clearBtns = document.querySelectorAll(".jfiltersClearBtn");

    clearBtns.forEach((clearBtn) => {
        clearBtn.addEventListener("click" , (event) => {
            const parentControlsWrapper = event.target.closest('.controls');
            // We do not just want to clear the input text, but also the modal`s url, not to include the previous filters.
            clearFiltersFromModalURL(parentControlsWrapper);
        })
    });

    function updateContextInModalURL(parentWrapper = document) {
        const contextEl = document.getElementById('jform_params_contextType');
        if (contextEl) {
            let JFiltersModalEl = parentWrapper.querySelector('.joomla-modal');
            let iframeData = JFiltersModalEl.dataset.iframe;
            let srcMatch = iframeData.match(/src="([^"]+)"/);

            // If a src attribute is found
            if (srcMatch && srcMatch[1]) {
                let modalURL = new URL(srcMatch[1]);
                modalURL.searchParams.set("filter[context]", contextEl.value);
                const newUrl = modalURL.toString();

                // Replace the src attribute value with the new URL
                let newIframeData = iframeData.replace(srcMatch[1], newUrl);

                // Update the data-iframe attribute value of the modal
                JFiltersModalEl.setAttribute("data-iframe", newIframeData);
            }
        }
    }

    function clearFiltersFromModalURL(parentWrapper = document) {
        let JFiltersModalEl = parentWrapper.querySelector('.joomla-modal');
        let iframeData =JFiltersModalEl.dataset.iframe;
        let srcMatch = iframeData.match(/src="([^"]+)"/);

        // If a src attribute is found
        if (srcMatch && srcMatch[1]) {
            let modalURL = new URL(decodeURIComponent(srcMatch[1]));
            let doNotDeleteParams = ["option", "view", "layout", "tmpl", "JFClient", "filter[context]"];
            let queryParams = Array.from(modalURL.searchParams.keys());
            queryParams.forEach(function(key) {
                if (!doNotDeleteParams.includes(key)) {
                    modalURL.searchParams.delete(key);
                }
            })
            const newUrl = modalURL.toString();
            let newIframeData = iframeData.replace(srcMatch[1], newUrl);
            JFiltersModalEl.setAttribute("data-iframe", newIframeData);
        }
    }

    updateContextInModalURL();
})