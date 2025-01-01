/**
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

'use strict';

// top namespace
window.JFilters = window.JFilters || {};

/*
Class: JFilters.range_sliders
        Creates a slider with two elements: a knob and a container. Returns the values.
Arguments:
        container - the container of the slider elements
        options - see Options below
Options:
		start - the minimum value for your slider.
		end - the maximum value for your slider.
		listen - calls the customFilters.listen() which triggers ajax requests.
		knobHeight
		knobWidth
		minDelta - The min. difference that min and max should have
*/
JFilters.range_sliders = class {
    static counter = 0;
    constructor(container, options) {
        this.options = {
            start: 0,
            end: 1000,
            listen: true,
            knobHeight: 20,
            knobWidth: 20,
            filterId: 0,
            moduleId: 0,
            withInputs: false,
            filterAlias: '',
            baseURL: '',
            urlFormat:'',
            minDelta: 1,
        }

        const el = '.jfilters-filter-range-sliders__gutter';
        const minKnob = '.jfilters-filter-range-sliders__knob--from';
        const maxKnob = '.jfilters-filter-range-sliders__knob--to';
        const bkg = '.jfilters-filter-range-sliders__slider_bckgr';

        this.setOptions(options);
        this.container = container;
        this.element = container.querySelector(el);
        this.minKnob = container.querySelector(minKnob);
        this.maxKnob = container.querySelector(maxKnob);
        this.bkg = container.querySelector(bkg);
        this.steps = parseInt(this.options.end) - parseInt(this.options.start);
        this.sliderWidth = this.minKnob.clientWidth;
        this.updateTooltip(this.minKnob);
        this.updateTooltip(this.maxKnob);
        this.fillRange();
        this.init();
    }

    setOptions(options) {
        for (const [key, value] of Object.entries(options)) {
            this.options[key] = value;
        }
    }

    init() {
        this.minKnob.addEventListener('input', () => {
            //const minInput = document.getElementById(this.key + '_0');
            const minValue = parseInt(this.minKnob.value);
            const maxValue = parseInt(this.maxKnob.value);

            //If the lower value slider is GREATER THAN the upper value slider minus one.
            if (minValue > maxValue - this.options.minDelta) {
                //The upper slider value is set to equal the lower value slider plus one.
                this.maxKnob.value = minValue + this.options.minDelta;
                //If the upper value slider equals its set maximum.
                if (maxValue == this.maxKnob.max) {
                    //Set the lower slider value to equal the upper value slider's maximum value minus one.
                    this.minKnob.value = parseInt(this.maxKnob.max) - this.options.minDelta;
                }
            }

            // Update the tooltip
            this.updateTooltip(this.minKnob);
            this.updateInputs();
            this.fillRange();
        })

        // Triggered when the min slide is finished
        this.handleMinKnobChange = this.updateModule.bind(this);
        this.maxKnob.removeEventListener('change', this.handleMinKnobChange);
        this.minKnob.addEventListener('change', this.handleMinKnobChange);

        this.maxKnob.addEventListener('input', () => {
            const minValue = parseInt(this.minKnob.value);
            const maxValue = parseInt(this.maxKnob.value);

            //If the upper value slider is LESS THAN the lower value slider plus one.
            if (maxValue < minValue + this.options.minDelta) {
                //The lower slider value is set to equal the upper value slider minus one.
                this.minKnob.value = maxValue - this.options.minDelta;
                //If the lower value slider equals its set minimum.
                if (minValue == this.minKnob.min) {
                    //Set the upper slider value to equal 1.
                    this.maxKnob.value = this.options.minDelta;
                }
            }

            // Update the tooltip
            this.updateTooltip(this.maxKnob);
            this.updateInputs();
            this.fillRange();
        })

        // Triggered when the max slide is finished
        this.handleMaxKnobChange = this.updateModule.bind(this);
        this.maxKnob.removeEventListener('change', this.handleMinKnobChange);
        this.maxKnob.addEventListener('change', this.handleMaxKnobChange);
    }

    updateModule() {
        if (this.options.listen && typeof JFilters.range_inputs.update == 'function') {
            JFilters.range_inputs.update(this.options, [this.minKnob.value, this.maxKnob.value]);
        }
    }

    setMin(minValue) {
        if (parseInt(minValue) + this.options.minDelta <= parseInt(this.maxKnob.value)) {
            this.minKnob.value = minValue;
            this.fillRange();
        }
        return this;
    }

    setMax(maxValue) {
        if (parseInt(maxValue) - this.options.minDelta >= parseInt(this.minKnob.value)) {
            this.maxKnob.value = maxValue;
            this.fillRange();
        }
        return this;
    }

    fillRange() {
        const minValue = parseInt(this.minKnob.value);
        const minLimit = parseInt(this.minKnob.min);
        const maxValue = parseInt(this.maxKnob.value);
        // Find px/step
        const ratio = this.sliderWidth / this.steps;
        const left = ratio * (minValue - minLimit);
        //Setting the margin left of the middle range color.
        this.bkg.style.marginLeft = left + "px";
        const right = ratio * (maxValue - minLimit);
        //Setting the width of the middle range color.
        this.bkg.style.width = right - left + "px";
    }
    updateTooltip(rangeElement) {
        if(typeof rangeElement.tooltipElement != 'undefined') {
            rangeElement.tooltipElement.setValue(rangeElement.value);
            rangeElement.tooltipElement.setCharLength(rangeElement.value.length);

            // Find px/step
            const ratio = this.sliderWidth / this.steps;
            const minLimit = parseInt(this.minKnob.min);
            const xPos = ratio * (parseInt(rangeElement.value) - minLimit);

            rangeElement.tooltipElement.positionX(xPos);
        }
    }

    updateInputs() {
        if (this.options.withInputs) {
            let inputsContainer = this.container.parentNode.querySelector('.jfilters-filter-range_inputs--withSlider');
            if (inputsContainer) {
                const inputs = [].slice.call(inputsContainer.querySelectorAll('.jfilters-filter-range_inputs__input'));
                if (inputs.length === 2) {
                    inputs[0].value = this.minKnob.value;
                    inputs[1].value = this.maxKnob.value;
                }
            }
        }
    }
}

JFilters.range_sliders.boot = () => {
    const filterParams = Joomla.getOptions('jfilters.filter', {}); // Return early
    if (typeof filterParams !== 'undefined') {
        filterParams.forEach(fltr => {
            if (!fltr.extraProperties || fltr.extraProperties.type != 'range_sliders') {
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
                start: filterConfig.minValue,
                end: filterConfig.maxValue,
                withInputs: filterConfig.withInputs,
            };

            let filterWrapperSelector = filterConfig.wrapperSelector ? filterConfig.wrapperSelector : '.jfilters-filter-range-sliders';
            const filterWrappers = [].slice.call(moduleElement.querySelectorAll(filterWrapperSelector));

            filterWrappers.forEach((filterWrapper) => {
                if (!filterWrapper.rangeSliders) {
                    filterWrapper.rangeSliders = new JFilters.range_sliders(filterWrapper, config);
                }
            });
        });
    }
}

document.addEventListener("DOMContentLoaded", JFilters.range_sliders.boot);