/**
* @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
* @license     GNU General Public License 2 or later, see COPYING.txt for license details.
*/

'use strict';

// top namespace
window.JFilters = window.JFilters || {};

JFilters.tooltip = class {
    constructor(attachElement, tipSelector) {
        this.attachElement = attachElement;
        if(Array.isArray(this.attachElement)) {
            this.attachElement = this.attachElement[0];
        }
        this.tipSelector = tipSelector;
        this.init();
    }
    init () {
        if (this.tipSelector.indexOf('#') !== -1) {
            this.tip = document.querySelector(this.tipSelector);
        } else {
            // check if it is close to the attachElement
            const adjacentEl = this.attachElement.nextElementSibling;
            // Check if it is tooltip
            if (this.tipSelector.indexOf('.') !== -1 && adjacentEl.classList.contains(this.tipSelector) || adjacentEl.classList.contains('jfilters-filter__tooltip')) {
                this.tip = adjacentEl;
            } else {
                this.tip = document.querySelector(this.tipSelector);
            }
        }
        this.positionX();
        return true;
    }
    getPosition () {
        let el = this.attachElement;
        return {
            top: el.offsetTop,
            left: el.offsetLeft
        }
    }
    positionX (x) {
        if(!x) {
            let position = this.getPosition();
            x = position.left;
        }
        this.tip.style.left = parseInt(x) - ((parseInt(this.tip.style.width)  *16) / 2) + 'px';
        this.tip.style.left;
    }
    positionY () {
        let position = this.getPosition();
        this.tip.style.top = parseInt(position.top) + 20     + 'px'
    }
    getValue () {
        let value = this.attachElement.getAttribute('data-tooltip');
        if(!value) {
            value = '';
        }
        return value;
    }
    setValue (value) {
        this.tip.innerHTML = value;
        this.attachElement.setAttribute('data-tooltip', value);
        this.attachElement.setAttribute('aria-valuenow', value);
        return true;
    }

    setCharLength(charLength) {
        if(!charLength || parseInt(charLength) < 2) {
            charLength = 2;
        }
        this.tip.style.width = charLength + 'rem';
    }
}

JFilters.tooltip.boot = () => {
    const tooltipAttachElements = [].slice.call(document.querySelectorAll('[data-tooltipElement]'));
    tooltipAttachElements.forEach((tooltipAttachElement) => {
        let tipId = tooltipAttachElement.getAttribute('data-tooltipElement');
        if (tipId) {
            /*
             Store the tooltip object to the attached element.
             This way the attached element can make changes to the tooltip (e.g. change its contents) by using the object's (JFilters.tooltip) functions.
             */
            tooltipAttachElement.tooltipElement = new JFilters.tooltip(tooltipAttachElement, '#' + tipId);
        }
    })
}

document.addEventListener("DOMContentLoaded", JFilters.tooltip.boot);