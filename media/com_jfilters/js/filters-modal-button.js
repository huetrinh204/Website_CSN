/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

document.addEventListener("DOMContentLoaded", function() {
    const contextEl = document.getElementById('jform_params_contextType');
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