/**
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

'use strict';

// top namespace
window.JFilters = window.JFilters || {};

JFilters.updater = {
    async updateComponent(url, isYOOTheme = 0) {
        // The baseURL is already encoded and seem to create a double encoding effect. See: https://blue-coder.com/help/support/jfilters/543:jfilters-not-updating-filters-on-date-range
        const baseURL = decodeURI(Joomla.getOptions('system.paths').baseFull);
        let resultsUrl = new URL(url, baseURL);
        resultsUrl.searchParams.set('tmpl', 'component');
        const resultsTargetSelector = '#jf_results';
        document.querySelector(resultsTargetSelector).classList.add('jf_results--loading');
        if (isYOOTheme) {
            // Clear previous filters from the session storage. Will be set again after the ajax call.
            if (Joomla.optionsStorage['jfilters.filter'] !== null && typeof Joomla.optionsStorage['jfilters.filter'] === 'object') {
                Joomla.optionsStorage['jfilters.filter'] = [];
            }
        }

        //Update the results
        await this.update(resultsUrl, resultsTargetSelector, '.joomla-script-options').then(() => {
            document.querySelector(resultsTargetSelector).classList.remove('jf_results--loading');
            // Get the options relevant the results
            let resultsOptions = Joomla.getOptions('jfilters.results');

            if (resultsOptions.title) {
                this.setTitle(resultsOptions.title);
            }
            this.setUrl(url, resultsOptions.title);

            // When we are in a YOOtheme template, the component area includes the modules.
            if (isYOOTheme) {
                // Update Filtering modules
                if (typeof JFilters.filteringModule != 'undefined' && typeof JFilters.filteringModule.boot != 'undefined') {
                    JFilters.filteringModule.boot();
                }
                // Update the Selections modules too
                if (typeof JFilters.selectionsModule != 'undefined' && typeof JFilters.selectionsModule.boot != 'undefined') {
                    JFilters.selectionsModule.boot();
                }
                if (typeof JFilters.calendar != 'undefined') {
                    JFilters.calendar.boot();
                }
                if (typeof JFilters.tooltip != 'undefined') {
                    JFilters.tooltip.boot();
                }
                if (typeof JFilters.range_inputs != 'undefined') {
                    JFilters.range_inputs.boot();
                }
                if (typeof JFilters.range_sliders != 'undefined') {
                    JFilters.range_sliders.boot();
                }
            }

           /*
           Dispatch an event after the results update, that can be used by other apps.
           Scripts relevant to the results can be triggered using that event.
           */
            document.dispatchEvent(new CustomEvent('JfResultsUpdate', {
                bubbles: true,
                cancelable: true,
                detail: {url: url}
            }));

            return true;
        });
    },
    async updateModule(url, moduleId, target) {
        // The baseURL is already encoded and seem to create a double encoding effect. See: https://blue-coder.com/help/support/jfilters/543:jfilters-not-updating-filters-on-date-range
        const baseURL = decodeURI(Joomla.getOptions('system.paths').baseFull);
        let urlObject = new URL(url, baseURL);
        urlObject.searchParams.set("view", "module");
        urlObject.searchParams.set("module_id", moduleId);
        urlObject.searchParams.set("tmpl", "component");
        return this.update(urlObject, target, '.joomla-script-options' , 'mod_jfilters_filters_' + moduleId);
    },

    async update(url, target, scriptSelector = 'script' , cssIdSelector = '') {
        const response = await this.fetchResource(url)
        if (response) {
            await this.injectResponse(response, target)
                .then(() => {
                    response.applyJs(scriptSelector);
                }).catch(e => {
                    console.log(`Error updating scripts": ` + e.message);
                }).then(() => {
                    response.applyCss(cssIdSelector);
                }).catch(e => {
                    console.log(`Error updating styles": ` + e.message);
                }).then(() => {
                    this.loadOptions();
                    return Promise.resolve();
                });
        }
    },
    fetchResource(url) {
        return fetch(url).then(response => {
            if(!response.ok) {
                throw new Error(`JFilters error! status: ${response.status} - ${response.statusText}`);
            } else {
                return response.text();
            }
        })
            .catch(e => {
                console.error(`JFilters error updating "${url}": ` + e.message);
            });
    },

    injectResponse(responseText, target) {
        let targetElement = document.querySelector(target);

        // Create a dummy object and inject the response in that, so that we can handle the response as dom.
        let tmpResponseWrapper = document.createElement("jf-temp-results-wrapper");
        tmpResponseWrapper.innerHTML = responseText;
        let responseElement = tmpResponseWrapper.querySelector(target);
        // If no target element the response is empty
        if(responseElement) {
            targetElement.innerHTML = responseElement.innerHTML;
        } else {
            targetElement.innerHTML = '';
        }
        tmpResponseWrapper.remove();
        return Promise.resolve();
    },
    loadOptions() {
        const optionsElement = document.querySelector('.joomla-script-options');
        // Replace the class to 'new', otherwise loadOptions will not work
        optionsElement.className = optionsElement.className.replace(' loaded', ' new');
        Joomla.loadOptions();
    },
    setTitle(title) {
        document.title = title;
    },
    setUrl(url, title) {
        window.history.pushState({"pageTitle":title},title, url);
    }
};

window.JFilters.filteringModule = {
    moduleWrapperSelectorPrefix : '#mod-jfilters_filters-',
    moduleSubmitButtonSelector : '.mod-jfilters_filters__submit-btn',
    filterHeaderSelector : '.jfilters-filter-header__toggle',
    filterInnerContainerSelector : '.jfilters-filter-container__inner',
    filterSearchContainerSelector : '.jfilters-filter-search'
};

window.JFilters.filteringModule.instances = window.JFilters.filteringModule.instances || {
    /**
     * Contains all the instances of the filtering modules
     */
};

JFilters.sessionStorageEnabled = JFilters.sessionStorageEnabled || function() {
    const test = 'testKey';

    try {
        sessionStorage.setItem(test, test);
        sessionStorage.removeItem(test);
        return true;
    } catch (e) {
        return false;
    }
};

JFilters.filteringModule.module = class {
    constructor(id, useAjax = 0, isYOOthemeTemplate = 0, submitWithButton = 0){
        this.id = id;
        this.useAjax = parseInt(useAjax);
        this.submitWithButton = parseInt(submitWithButton);
        this.isPro = false;
        this.isYOOthemeTemplate = parseInt(isYOOthemeTemplate);
        this.filters = [];
        this.moduleWrapper = document.querySelector(window.JFilters.filteringModule.moduleWrapperSelectorPrefix + this.id);
        JFilters.filteringModule.instances[this.id] = this;
        this.init();
    }

    init() {
        // Update component using the submit button
        const submitButton = this.moduleWrapper ? this.moduleWrapper.querySelector(window.JFilters.filteringModule.moduleSubmitButtonSelector) : null;

        if (this.submitWithButton && submitButton) {
            submitButton.addEventListener('click', () => {
                const url = submitButton.dataset.url;
                if (url) {
                    if (this.useAjax) {
                        document.dispatchEvent(new CustomEvent('JfResultsUpdateStart', {
                            bubbles: true,
                            cancelable: true,
                            detail: {moduleId: this.id, url: url}
                        }));
                        JFilters.updater.updateComponent(url, this.isYOOthemeTemplate);
                    }else {
                        const baseURL = decodeURI(Joomla.getOptions('system.paths').baseFull);
                        let resultsUrl = new URL(url, baseURL);
                        window.location = resultsUrl;
                    }
                }
            });
        }
    }

    registerFilter(filter) {
        this.filters[filter.id] = filter;
    }

    update(url) {
        // Clear previous filters from the session storage. Will be set again after the ajax call.
        if (Joomla.optionsStorage['jfilters.filter'] !== null && typeof Joomla.optionsStorage['jfilters.filter'] === 'object') {
            Joomla.optionsStorage['jfilters.filter'] = [];
        }

        const targetSelector = window.JFilters.filteringModule.moduleWrapperSelectorPrefix + this.id;
        /*
         * When we are in a YOOtheme template, the component area includes the modules.
         * Hence no need to fetch the module.
         */
        if(!this.isYOOthemeTemplate || this.submitWithButton) {
            const submitButton = this.submitWithButton ? this.moduleWrapper.querySelector(window.JFilters.filteringModule.moduleSubmitButtonSelector) : null;
            if (submitButton) {
                // Set the btn as disabled and show loading dots, while the module is being updated.
                submitButton.setAttribute('disabled', 'true');
                const labelElement = submitButton.querySelector('.jfilters_button__label');

                if (labelElement) {
                    labelElement.classList.add('jfilters_button__label--hide');
                }
                const loaderElement = submitButton.querySelector('.jfilters__loadMore_dots');
                if (loaderElement) {
                    loaderElement.classList.remove('jfilters__loadMore_dots--hide');
                }
            }
            JFilters.updater.updateModule(url, this.id, targetSelector).then(() => {
                // Reset button state
                if (submitButton) {
                    submitButton.removeAttribute('disabled');
                    const labelElement = submitButton.querySelector('.jfilters_button__label');
                    if (labelElement) {
                        labelElement.classList.remove('jfilters_button__label--hide');
                    }
                    const loaderElement = submitButton.querySelector('.jfilters__loadMore_dots');
                    if (loaderElement) {
                        loaderElement.classList.add('jfilters__loadMore_dots--hide');
                    }
                }

                // Boot the new module and its filters
                JFilters.filteringModule.boot('', this.id);
                if(typeof JFilters.calendar != 'undefined') {
                    JFilters.calendar.boot();
                }
                if(typeof JFilters.tooltip != 'undefined') {
                    JFilters.tooltip.boot();
                }
                if(typeof JFilters.range_inputs != 'undefined') {
                    JFilters.range_inputs.boot();
                }
                if(typeof JFilters.range_sliders != 'undefined') {
                    JFilters.range_sliders.boot();
                }

                // Dispatch an event that can be used by other apps
                document.dispatchEvent(new CustomEvent('JfFilteringModuleUpdate', {
                    bubbles: true,
                    cancelable: true,
                    detail: {moduleId: this.id, url: url}
                }))
            });
        }
        else {
            // Boot the new module and it's filters
            JFilters.filteringModule.boot('', this.id);
        }
    }
};

JFilters.filteringModule.Filter = class {

    constructor(id, properties, moduleId){
        this.filterWrapper = document.getElementById('jfilters-filter-container-'+moduleId+'-'+id);

        // If it does not exist do not proceed
        if(this.filterWrapper === null) {
            return false;
        }

        this.id = id;
        this.moduleId = moduleId;
        this.filterKey = 'jfilters/filter/' + this.moduleId + '/'+this.id;
        let propertiesTmp = JSON.parse(properties);
        for (let key in propertiesTmp) {
            this[key] = propertiesTmp[key];
        }

        // Exit if the module does not exist.
        if(!JFilters.filteringModule.instances[moduleId]) {
            return false;
        }
        JFilters.filteringModule.instances[moduleId].isPro = this.isPro;
        JFilters.filteringModule.instances[moduleId].registerFilter(this);
        this.init();
    }

    init () {
        this.loadExpandedByLocalStorage();
        this.filterWrapper.querySelector(window.JFilters.filteringModule.filterHeaderSelector).addEventListener('click', () => this.toggleFilter());
        this.setAjaxUpdateEvents();

        if (this.filterWrapper.querySelector('.dropdown')) {
            this.handleBootstrapDropDown();
        }

        // add the functionality for the trees
        if(this.isTree) {
            let toggleElements = [].slice.call(this.filterWrapper.querySelectorAll('.jfilters-item__toggle-btn'));
            if(!this.parent_node_linkable) {
                let links = [].slice.call(this.filterWrapper.querySelectorAll('.jfilters-item-link[aria-expanded]'));
                toggleElements = toggleElements.concat(links);
            }

            toggleElements.forEach(el => {
                el.addEventListener('click', (event) => {
                    event.preventDefault();
                    this.toggleTree(el)
                });
            });
        }
    }

    setAjaxUpdateEvents() {
        if(this.isPro && JFilters.filteringModule.instances[this.moduleId].useAjax || JFilters.filteringModule.instances[this.moduleId].submitWithButton) {
            const innerWrapper = this.filterWrapper.querySelector(window.JFilters.filteringModule.filterInnerContainerSelector);
            if(innerWrapper) {
                const links = [].slice.call(innerWrapper.querySelectorAll('a:not([aria-controls])'));
                links.forEach(link => {
                    link.addEventListener('click' , (event) => {
                        const url = link.closest('a').getAttribute('href');
                        event.preventDefault();
                        event.stopPropagation();
                        this.update(url, !JFilters.filteringModule.instances[this.moduleId].submitWithButton);
                    });
                });
            }
        }
    }

    update(url, updateComponent = true) {
        if(this.isPro && (JFilters.filteringModule.instances[this.moduleId].useAjax || JFilters.filteringModule.instances[this.moduleId].submitWithButton)) {

            // Update the module
            JFilters.filteringModule.instances[this.moduleId].update(url);

            // update the component
            if (updateComponent) {
                document.dispatchEvent(new CustomEvent('JfResultsUpdateStart', {
                    bubbles: true,
                    cancelable: true,
                    detail: {moduleId: this.moduleId, url: url}
                }));
                JFilters.updater.updateComponent(url, JFilters.filteringModule.instances[this.moduleId].isYOOthemeTemplate);
            }

            // Dispatch an event that can be used by other apps
            document.dispatchEvent(new CustomEvent('JfFilteringLinkClick', {
                bubbles: true,
                cancelable: true,
                detail: {moduleId: this.moduleId, url: url}
            }));

            return true;
        }
        return false;
    }

    loadExpandedByLocalStorage() {
        let storedExpandedState = this.getStorageVariable('hidden');
        const innerWrapper = this.filterWrapper.querySelector(window.JFilters.filteringModule.filterInnerContainerSelector);
        if(storedExpandedState && innerWrapper && innerWrapper.getAttribute('aria-hidden') != storedExpandedState) {
            this.toggleFilter();
        }
    }

    toggleFilter () {
        const innerWrapper = this.filterWrapper.querySelector(window.JFilters.filteringModule.filterInnerContainerSelector);
        if(innerWrapper) {
            let currentState = innerWrapper.getAttribute('aria-hidden');
            let newState = currentState == 'true' ? 'false' : 'true';
            innerWrapper.setAttribute('aria-hidden', newState);
            const headerElement = this.filterWrapper.querySelector(window.JFilters.filteringModule.filterHeaderSelector);
            if(headerElement) {
                // the aria-expanded should have the opposite value to the aria-hidden
                headerElement.setAttribute('aria-expanded', newState=='true'?'false':'true');
            }
            this.setStorageVariable('hidden', newState);
        }
    }

    toggleTree(element, updateSubTree = true) {
        let subTree= element.closest('li').querySelector('ul');

        if(subTree) {
            let expanded = element.getAttribute('aria-expanded');
            let newExpanded = expanded == 'true' ? 'false': 'true';
            element.setAttribute('aria-expanded', newExpanded);

            if(updateSubTree) {
                // Update the arias of the adjacent element
                let closestElement = element.nodeName == 'BUTTON' ? element.closest('li').querySelector('a[aria-controls]') : element.closest('li').querySelector('button[aria-controls]');
                if(closestElement) {
                    this.toggleTree(closestElement, false);
                }
                // aria-hidden value is the opposite of the aria-expanded value
                let ariaHidden = newExpanded == 'true' ? 'false' : 'true';
                subTree.setAttribute('aria-hidden', ariaHidden);
            }
            return true;
        }
        return false;
    }

    handleBootstrapDropDown() {
        let dropdown = this.filterWrapper.querySelector('.dropdown');
        let searchContainer = this.filterWrapper.querySelector(window.JFilters.filteringModule.filterSearchContainerSelector);

        if (dropdown && searchContainer) {
            let searchInput = searchContainer.querySelector('input[type="text"]');
            let labelContainer = this.filterWrapper.querySelector('.jfilters-filter-dropdown-toggle__label');
            let clearButton =  this.filterWrapper.querySelector('.jfilters-filter-dropdown__clear');
            let hideSearchClass = 'jfilters-filter-search--hide';
            let hideLabelClass = 'jfilters-filter-dropdown-toggle__label--hide';
            let hideClearButton = 'jfilters-filter-dropdown__clear--hide';

            dropdown.addEventListener('show.bs.dropdown', function () {
                // We want a toggle effect that keeps visible either the label (drop-down closed) or the search (drop-down open).
                labelContainer.classList.add(hideLabelClass);
                searchContainer.classList.remove(hideSearchClass)
                if (clearButton) {
                    clearButton.classList.add(hideClearButton);
                }
            })

            dropdown.addEventListener('hidden.bs.dropdown', function () {
                // Do the toggling only when there is a selection. Otherwise it has no meaning. Keep displaying the search.
                if (!labelContainer.classList.contains('jfilters-filter-dropdown-toggle__label--noSelection')) {
                    labelContainer.classList.remove(hideLabelClass);
                    searchContainer.classList.add(hideSearchClass);
                    if (clearButton) {
                        clearButton.classList.remove(hideClearButton);
                    }
                }
                // Clear any search when the drop-down closes
                searchInput.value='';
                const inputEvent = new InputEvent('input', {
                    bubbles: true,
                    cancelable: true,
                });

                // Dispatch the input event for the element
                searchInput.dispatchEvent(inputEvent);
            })

            dropdown.addEventListener('shown.bs.dropdown', function () {
                // Set focus back to the search
                searchInput.focus();
            })
        }
    }

    getStorageVariable(varName) {
        if(JFilters.sessionStorageEnabled()) {
            let key = this.filterKey + '/' + varName;
            return sessionStorage.getItem(key);
        }
        // means no sessionStorage Supported
        return false;
    }

    setStorageVariable(varName, varValue) {
        if(JFilters.sessionStorageEnabled()) {
            let key = this.filterKey + '/' + varName;
            sessionStorage.setItem(key, varValue);
            return true;
        }
        // means no sessionStorage Supported
        return false;
    }
    createUrl(values, filterBaseUrl, filterAlias, urlFormat = 'path')
    {
        // values should be array
        values = Array.isArray(values) ? values : [values];
        let url = filterBaseUrl;
        if(urlFormat == 'path' && url.indexOf('?') === -1) {
            url += '/' + filterAlias + '/' + values.join('|');
        }
        else {
            let i = 0;
            values.forEach((date) => {
                let delimiter = (i === 0 && url.indexOf('?') === -1) ? '?' : '&';
                url += delimiter + filterAlias + `[${i}]=` + date;
                i ++;
            });
        }

        return url;
    }
};

JFilters.listSearch = class {
    /**
     *
     * @param {JFilters.filteringModule.Filter} filter
     * @param {Object} options
     */
    constructor(filter , options= {}) {
        this.options = {
            cache: true,
            caseSensitive: false,
            ignoreKeys: [13, 27, 32, 37, 38, 39, 40],
            matchAnywhere: true,
            optionClass: ".jfilters-item__label-text",
            trigger: "input"
        };
        this.setOptions(options);
        this.filterWrapper = document.getElementById('jfilters-filter-container__inner-'+filter.moduleId+'-'+filter.id);
        this.observeElement = this.filterWrapper.querySelector('.jfilters-filter-search__input');
        this.elements = [].slice.call(this.filterWrapper.querySelectorAll('.jfilters-filter-list__item'));

        // Check if they are drop-down list elements
        if (this.elements.length === 0) {
            this.elements = [].slice.call(this.filterWrapper.querySelectorAll('.jfilters-filter-dropdown__item'));
        }
        this.matches = this.elements;
        if(this.observeElement) {
            this.listen();
        }
    }

    setOptions(options) {
        for (const [key, value] of Object.entries(options)) {
            this.options[key] = value;
        }
    }

    listen () {
        this.observeElement.addEventListener(this.options.trigger, function (e) {
            if (this.observeElement.value.length) {
                if (!this.options.ignoreKeys.includes(e.code)) {
                    this.start();
                    this.findMatches(this.options.cache ? this.matches : this.elements);
                }
            } else {
                this.elements.forEach((element) => {
                    element.classList.remove('jfilters-filter-list__item--hidden');
                })
                this.findMatches(this.elements, true);
                this.clearHtmlFromText(this.elements);

                // Hide the clear btn
                let clearBtn = this.filterWrapper.querySelector('.jfilters-filter-search__clear');
                clearBtn.classList.add('jfilters-filter-search__clear--hidden');

                // Hide visible nested ULs (made visible while searching)
                this.hideHiddenLists();

            }
        }.bind(this))
    }

    start (){
        this.elements.forEach((element) => {
            element.classList.add('jfilters-filter-list__item--hidden');
        });

        // Show the clear btn
        let clearBtn = this.filterWrapper.querySelector('.jfilters-filter-search__clear');
        clearBtn.classList.remove('jfilters-filter-search__clear--hidden');
        clearBtn.addEventListener('click', (event) => {
            event.preventDefault();
            // Prevent BS drop-downs closing on click
            event.stopPropagation();
            this.observeElement.value = '';
            this.findMatches(this.elements, true);
            this.clearHtmlFromText(this.elements);
            clearBtn.classList.add('jfilters-filter-search__clear--hidden');
            this.hideHiddenLists();
        });
    }

    showHiddenLists () {
        // Make the hidden sub-trees visible to be able to search in them
        let hiddenLists = [].slice.call(this.filterWrapper.querySelectorAll('.jfilters-filter-list [aria-hidden="true"]'));
        hiddenLists.forEach((hiddenList) => {
            hiddenList.classList.add('jfilters-filter-list--visible');
        });
    }

    hideHiddenLists () {
        // Make the hidden sub-trees invisible
        let hiddenLists = [].slice.call(this.filterWrapper.querySelectorAll('.jfilters-filter-list--visible'));
        hiddenLists.forEach((hiddenList) => {
            hiddenList.classList.remove('jfilters-filter-list--visible');
        });
    }

    show (element) {
        element.classList.remove('jfilters-filter-list__item--hidden');
        let parent = element.closest('.jfilters-filter-list__item--hidden');
        if (parent) {
            this.show(parent);
        }
    }

    hide (element){
        element.classList.add('jfilters-filter-list__item--hidden');
    }

    matchText (element){
        let user_input = this.observeElement.value;
        //the text part of the element
        const textElement = element.querySelector(this.options.optionClass);
        const text = textElement.textContent;

        //convert all to lower case to achieve the matching and get the start char
        const text_lc = text.toLowerCase();
        const user_input_lc = user_input.toLowerCase();
        let start_char = text_lc.indexOf(user_input_lc);
        if (start_char === -1) {
            const text_normalized = text_lc.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            start_char = text_normalized.indexOf(user_input_lc);
        }
        //get the part from the list element-not from the input. Because of the letter case the user uses in the input
        const part = text.substring(start_char, start_char + user_input.length);
        //wrap the part
        textElement.innerHTML = text.replace(part, '<span class="jfilters-item__text--search-match">' + part + '</span>');
    }

    findMatches (elements, defaultMatching) {
        let user_input = this.observeElement.value;
        // Escape special characters
        const chars = {'+':'\\+', '*': '\\*', '.':'\\.', '(' : '\\(', ')': '\\)', '|': '\\|' , '?' : '\\?', '^': '\\^', '$' : '\\$'};
        user_input = user_input.replace(/[\+\*\.\(\)\|\?,\^,\$]/g, m => chars[m]);
        const user_input2 = this.options.matchAnywhere ? user_input : "^" + user_input;
        const caseSensitive = this.options.caseSensitive ? "" : "i";
        const regex = new RegExp(user_input2, caseSensitive);

        this.showHiddenLists();

        elements.forEach((element) => {
            const textElement = element.querySelector(this.options.optionClass);//the text part of the element
            let text = textElement.textContent;
            const isClear = element.classList.contains("jfilters-item-link--clear");
            let isMatch = defaultMatching === undefined ? regex.test(text) : defaultMatching;
            if (!isClear && !isMatch) {
                // check after removing intonation
                text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                isMatch = regex.test(text)
            }

            if (isMatch && (isClear=== false || defaultMatching === true)) {
                this.matchText(element);
                this.show(element);
            } else {
                this.hide(element);
            }
            return true;
        });
    }

    /**
     * Clear all the html tags from the text/labels of the values
     * @param Array elements
     */
    clearHtmlFromText(elements) {
        elements.forEach((element) => {
            const textElement = element.querySelector(this.options.optionClass);//the text part of the element
            const text = textElement.textContent;//strip html code
            textElement.innerHTML = text;
            this.hideHiddenLists();
        });
    }
}

/**
 * Generate the filters from the json object 'jfilters.filter' of the document.
 * That json string should include any information we need in the scripts.
 * Any logic should be implemented in that file.
 */
JFilters.filteringModule.boot = (event, id = '') => {
    if (!window.JFilters || !window.JFilters.filteringModule || typeof window.JFilters.filteringModule !== 'object') {
        throw new Error('The JFilters API is not correctly registered.');
    }

    const moduleParams = Joomla.getOptions('jfilters.filteringModule'); // Return early
    if (typeof moduleParams !== 'undefined') {
        moduleParams.forEach(module => {
            if(id && module.id != id) {
                // Continue to the next one
                return;
            }
            new JFilters.filteringModule.module(module.id, module.ajax_mode, module.isYOOthemeTemplate, module.submit_filters_using_button);
        });
    }

    const filterParams = Joomla.getOptions('jfilters.filter'); // Return early
    if (typeof filterParams !== 'undefined') {
        filterParams.forEach(fltr => {
            if(id && fltr.moduleId != id) {
                // Continue to the next one
                return;
            }
            let filter = new JFilters.filteringModule.Filter(fltr.id, fltr.properties, fltr.moduleId);
            new JFilters.listSearch(filter);
        });
    }
};

String.prototype.applyJs = function(selector){

    // Create a dummy object and inject the response in that, so that we can handle the response as dom.
    const tmpResponseWrapper = document.createElement("jf-temp-js-wrapper");
    tmpResponseWrapper.innerHTML = this;
    let scriptElements = [].slice.call(tmpResponseWrapper.querySelectorAll(selector));
    tmpResponseWrapper.remove();

    // apply the js
    if (scriptElements && scriptElements.length == 1) {
        const scriptSelector = document.querySelector(selector);

        // Append the new script to the existing selector
        if(scriptSelector) {
            const scriptType = scriptSelector.getAttribute('type');

            //  Merge previous and current json
            if(scriptType == 'application/json') {
                try {
                    const oldJSon = Joomla.optionsStorage;
                    const newJson = JSON.parse(scriptElements[0].text || scriptElements[0].textContent);

                    /*When we are dealing with filtering objects,
                    * make sure that it overrides only those of the current module.
                    * The page may contains several modules.
                    */
                    let key = 'jfilters.filter';
                    if (newJson[key] !== null && typeof newJson[key] === 'object' && oldJSon[key] !== null && typeof oldJSon[key] === 'object') {
                        newJson[key].forEach((newFilter, newIndex) => {
                            let found = false;
                            oldJSon[key].every((filter, index) => {
                                if (filter.id === newFilter.id && filter.moduleId === newFilter.moduleId) {
                                    found = true;
                                    return;
                                }
                            });
                            if(!found) {
                                oldJSon[key].push(newFilter);
                            }
                        });
                        newJson[key] = null;
                    }

                    // Replace the old 'jfilters.results' with the new
                    key = 'jfilters.results';
                    if (newJson[key] !== null && typeof newJson[key] === 'object' && oldJSon[key] !== null && typeof oldJSon[key] === 'object') {
                        oldJSon[key] = newJson[key];
                    }

                    // Marge previous with new
                    const mergeJson = {
                        ...newJson,
                        ...oldJSon
                    };

                    // We have to clean the stored options. Otherwise, appends the new options to the previous.
                    Joomla.optionsStorage = {};

                    scriptSelector.text = JSON.stringify(mergeJson);
                } catch (e) {
                    console.error(e);
                    throw e;
                }
            }
            else {
                scriptSelector.innerHTML += scriptPlain;
            }
        }
    }
    return this;
};

String.prototype.applyCss = function(cssIdSelector){

    if(!cssIdSelector) {
        return false;
    }

    // Create a dummy object and inject the response in that, so that we can handle the response as dom.
    const tmpResponseWrapper = document.createElement("jf-temp-js-wrapper");
    tmpResponseWrapper.innerHTML = this;
    const styleElementTmp = tmpResponseWrapper.querySelector('#' + cssIdSelector);
    tmpResponseWrapper.remove();

    // apply the css for the module
    if (styleElementTmp) {
        const css = styleElementTmp.innerHTML;
        const styleElement = document.querySelector('#' + cssIdSelector);

        if(!styleElement && css) {
            let style = document.createElement('style');
            style.id = cssIdSelector;
            style.appendChild(document.createTextNode(css));
            let head = document.head || document.getElementsByTagName('head')[0];
            head.appendChild(style);
        } else {
            styleElement.innerHTML = css;
        }
    }
    return true;
};



document.addEventListener('DOMContentLoaded', JFilters.filteringModule.boot);

// Listen to selections in the selection modules
document.addEventListener('JfSelectionsLinkClick', function (event){
    if(event.detail && event.detail.url) {
        const moduleParams = Joomla.getOptions('jfilters.filteringModule'); // Return early
        if (typeof moduleParams !== 'undefined') {
            moduleParams.forEach(module => {
                // YOOtheme updates the entire page
                if(!module.isYOOthemeTemplate) {
                    JFilters.filteringModule.instances[module.id].update(event.detail.url);
                }
            });
        }
    }
});

// Listen to selections in the filtering modules and update other than the selected modules
document.addEventListener('JfResultsUpdateStart', function (event){
    if(event.detail && event.detail.url && event.detail.moduleId) {
        const moduleParams = Joomla.getOptions('jfilters.filteringModule'); // Return early
        if (typeof moduleParams !== 'undefined') {
            moduleParams.forEach(module => {
                // Update other than the selected
                if(module.id == event.detail.moduleId || module.isYOOthemeTemplate) {
                    return;
                }
                JFilters.filteringModule.instances[module.id].update(event.detail.url);
            });
        }
    }
});

// Reload the page, when we go back and forth using the history API
window.addEventListener('popstate', () => {
    if (!location.hash) {
        location.reload();
    }
});

