// noinspection DuplicatedCode

/**
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

'use strict';

// top namespace
window.JFilters = window.JFilters || {};

JFilters.range_inputs = {}

JFilters.range_inputs.update = (config, values) => {

    // Construct the url
    const moduleId = config.moduleId;
    const filterId = config.filterId;
    const baseUrl = config.baseUrl;
    const filterAlias = config.filterAlias;
    if(!baseUrl || !filterAlias) {
        return false;
    }
    const urlFormat = config.urlFormat ? config.urlFormat : 'path';
    let url = '';

    // Get the url based on the selected values
    url = JFilters.filteringModule.instances[moduleId].filters[filterId].createUrl(values, baseUrl, filterAlias, urlFormat);
    let pageUpdated = false;
    if(JFilters.filteringModule.instances[moduleId].filters[filterId]) {
        pageUpdated = JFilters.filteringModule.instances[moduleId].filters[filterId].update(url, !JFilters.filteringModule.instances[moduleId].submitWithButton);
    }

    if(!pageUpdated) {
        window.location.assign(url);
    }
    return true;
}

JFilters.range_inputs.getValues = (filterWrapper) => {
    let values = {};
    const inputs = [].slice.call(filterWrapper.querySelectorAll('.jfilters-filter-range_inputs__input'));
    inputs.forEach((input, index) => {
        let key = index == 0 ? 'min' : 'max';
        values[key] = input.value;
    });
    return values;
}

JFilters.range_inputs.setValues = (filterWrapper, values) => {
    const inputs = [].slice.call(filterWrapper.querySelectorAll('.jfilters-filter-range_inputs__input'));
    inputs.forEach((input, index) => {
        let key = index == 0 ? 'min' : 'max';
        input.value = values[key];
    });
    return true;
}

JFilters.range_inputs.validateInput = (filterWrapper, actualIndex) => {
    let isValid = true;
    let alertMessage = '';
    let values = JFilters.range_inputs.getValues(filterWrapper);
    const isNumericMin = values.min.match(/^[+-]?\d+(\.\d*)?$/);
    const isNumericMax = values.max.match(/^[+-]?\d+(\.\d*)?$/);
    const submitBtn = filterWrapper.querySelector('.jfilters-filter__submit-btn');

    if (isNumericMin || isNumericMax) {
        if (submitBtn) {
            submitBtn.removeAttribute("disabled");
        }
        alertMessage = '';
    }
    // Invalid
    else {
        if (submitBtn) {
            submitBtn.setAttribute("disabled", "disabled");
        }

        let isActualNumeric = actualIndex === 'min' ? isNumericMin : isNumericMax;

        if (!isActualNumeric) {
            alertMessage = Joomla.Text._('MOD_JFILTERS_FILTER_ALERT_MSG_ONLY_NUMERICAL_VALUES');
        }
        isValid = false;
    }

    const alertMessageElement = filterWrapper.parentElement.querySelector('.jfilters-filter__alert');
    if (alertMessageElement) {
        alertMessageElement.innerText = alertMessage;
    }

    return isValid;
}

JFilters.range_inputs.validateRange = (filterWrapper, config) => {
    let isValid = true;
    let alertMessage = '';
    let values = JFilters.range_inputs.getValues(filterWrapper);

    if (parseFloat(values.min) > parseFloat(values.max)) {
        isValid = false;
        alertMessage = Joomla.Text._('MOD_JFILTERS_FILTER_ALERT_MSG_MAX_LOWER_TO_MIN');
    }

    // Validate by min and max limits
    if (config.minLimit != '' && config.maxLimit != '' && (
        (parseFloat(values.min) < config.minLimit || parseFloat(values.min) > config.maxLimit) ||
        (parseFloat(values.max) < config.minLimit || parseFloat(values.max) > config.maxLimit))
    ) {
        isValid = false;
        alertMessage = Joomla.Text._('MOD_JFILTERS_FILTER_ALERT_MSG_INSERTED_VALUES_BETWEEN');
        alertMessage = alertMessage.replace(/%[ds]/, config.minLimit);
        alertMessage = alertMessage.replace(/%[ds]/, config.maxLimit);
    }

    else if (config.minLimit != '' && config.maxLimit == '' &&
        (parseFloat(values.min) < config.minLimit || parseFloat(values.max) < config.minLimit)) {
        isValid = false;
        alertMessage = Joomla.Text._('MOD_JFILTERS_FILTER_ALERT_MSG_INSERTED_VALUES_HIGHER_THAN').replace(/%[ds]/, config.minLimit);
    }

    else if (config.minLimit == '' && config.maxLimit != '' &&
        (parseFloat(values.min) > config.maxLimit || parseFloat(values.max) > config.maxLimit)) {
        isValid = false;
        alertMessage = Joomla.Text._('MOD_JFILTERS_FILTER_ALERT_MSG_INSERTED_VALUES_LOWER_THAN').replace(/%[ds]/, config.maxLimit);
    }

    const alertMessageElement = filterWrapper.parentElement.querySelector('.jfilters-filter__alert');
    if (alertMessageElement) {
        alertMessageElement.innerText = alertMessage;
    }

    return isValid;
}

JFilters.range_inputs.boot = () => {
    const filterParams = Joomla.getOptions('jfilters.filter'); // Return early
    if (typeof filterParams !== 'undefined') {
        filterParams.forEach(fltr => {
            // We use the same config for range sliders and inputs. Could be 1 filter using both displays
            if (!fltr.extraProperties || !(fltr.extraProperties.type == 'range_inputs' || (fltr.extraProperties.type == 'range_sliders' && fltr.extraProperties.withInputs === true))) {
                // Continue to the next one
                return;
            }
            let filterConfig = fltr.extraProperties;
            const moduleSelector = window.JFilters.filteringModule.moduleWrapperSelectorPrefix + fltr.moduleId;
            const moduleElement = document.querySelector(moduleSelector);
            if (!moduleElement) {
                return;
            }

            let config = {
                filterId: fltr.id,
                moduleId: fltr.moduleId,
                filterAlias: filterConfig.alias,
                baseUrl: filterConfig.baseUrl,
                urlFormat: filterConfig.urlFormat,
                minLimit: filterConfig.minValue,
                maxLimit: filterConfig.maxValue,
            };

            const filterWrapper = moduleElement.querySelector('#jfilters-filter-range_inputs-' + fltr.moduleId + '-' + fltr.id);
            if (!filterWrapper.rangeInputs) {
                JFilters.range_inputs.addEvents(filterWrapper);
                const submitBtn = filterWrapper.querySelector('.jfilters-filter__submit-btn');
                if (submitBtn) {
                    submitBtn.addEventListener('click', () => {
                        const isValidRange = JFilters.range_inputs.validateRange(filterWrapper, config);

                        if (isValidRange) {
                            let values = JFilters.range_inputs.getValues(filterWrapper);
                            values = [values.min, values.max];
                            JFilters.range_inputs.update(config, values);
                        }
                    })
                }
            }
            filterWrapper.rangeInputs = true;
        });
    }
}
JFilters.range_inputs.addEvents = (filterWrapper) => {
    const inputs = [].slice.call(filterWrapper.querySelectorAll('.jfilters-filter-range_inputs__input'));
    const fromElement = inputs.length === 2 ? inputs[0] : null;
    const toElement = inputs.length === 2 ? inputs[1] : null;
    const withSlider = filterWrapper.classList.contains('jfilters-filter-range_inputs--withSlider');
    if (fromElement && toElement) {
        fromElement.addEventListener("input", function () {
            const validRange = JFilters.range_inputs.validateInput(filterWrapper, 'min');
            if (validRange && withSlider) {
                // Update min. range slider.
                const slidersContainer = filterWrapper.parentNode.querySelector('.jfilters-filter-range-sliders');
                if (slidersContainer && typeof slidersContainer.rangeSliders != 'undefined') {
                    let values = JFilters.range_inputs.getValues(filterWrapper);
                    slidersContainer.rangeSliders.setMin(values.min);
                }
            }
            fromElement.listenerAttached = true;
        });
        toElement.addEventListener("input", function () {
            const validRange = JFilters.range_inputs.validateInput(filterWrapper, 'max');
            if (validRange && withSlider) {
                // Update max. range slider.
                // Update min. range slider.
                const slidersContainer = filterWrapper.parentNode.querySelector('.jfilters-filter-range-sliders');
                if (slidersContainer && typeof slidersContainer.rangeSliders != 'undefined') {
                    let values = JFilters.range_inputs.getValues(filterWrapper);
                    slidersContainer.rangeSliders.setMax(values.max);
                }
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", JFilters.range_inputs.boot);