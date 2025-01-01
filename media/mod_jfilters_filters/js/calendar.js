/**
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

'use strict';

// top namespace
window.JFilters = window.JFilters || {};

JFilters.calendar = {}

JFilters.calendar.update = (flatpkrInstance, valueString) => {

    // Construct the url
    const moduleId = flatpkrInstance.config.moduleId;
    const filterId = flatpkrInstance.config.filterId;
    const baseUrl = flatpkrInstance.config.baseUrl;
    const filterAlias = flatpkrInstance.config.filterAlias;
    if(!baseUrl || !filterAlias) {
        return false;
    }
    const urlFormat = flatpkrInstance.config.urlFormat ? flatpkrInstance.config.urlFormat : 'path';
    let url = '';
    let dates = [];

    if(flatpkrInstance.config.mode == 'range') {
        // We need 2 dates (even similar) to proceed
        if(flatpkrInstance.selectedDates.length < 2) {
            return false;
        }
        dates = valueString.split(flatpkrInstance.l10n.rangeSeparator);
    } else {
        dates = [valueString];
    }

    // Get the url based on the selected values
    url = JFilters.filteringModule.instances[moduleId].filters[filterId].createUrl(dates, baseUrl, filterAlias, urlFormat);
    let pageUpdated = false;
    if(JFilters.filteringModule.instances[moduleId].filters[filterId]) {
        pageUpdated = JFilters.filteringModule.instances[moduleId].filters[filterId].update(url, !JFilters.filteringModule.instances[moduleId].submitWithButton);
    }

    if(!pageUpdated) {
        window.location.assign(url);
    }
    return true;
}

/**
 * Set the locale as passed by the layout from Joomla translation vars
 */
JFilters.calendar.localize = (config) => {
    let JoomlaLocale = {
        weekdays: {
            shorthand: [
                Joomla.Text._('SUN'),
                Joomla.Text._('MON'),
                Joomla.Text._('TUE'),
                Joomla.Text._('WED'),
                Joomla.Text._('THU'),
                Joomla.Text._('FRI'),
                Joomla.Text._('SAT'),
            ],
            longhand: [
                Joomla.Text._('SUNDAY'),
                Joomla.Text._('MONDAY'),
                Joomla.Text._('TUESDAY'),
                Joomla.Text._('WEDNESDAY'),
                Joomla.Text._('THURSDAY'),
                Joomla.Text._('FRIDAY'),
                Joomla.Text._('SATURDAY'),
            ],
        },
        months: {
            shorthand: [
                Joomla.Text._('JANUARY_SHORT'),
                Joomla.Text._('FEBRUARY_SHORT'),
                Joomla.Text._('MARCH_SHORT'),
                Joomla.Text._('APRIL_SHORT'),
                Joomla.Text._('MAY_SHORT'),
                Joomla.Text._('JUNE_SHORT'),
                Joomla.Text._('JULY_SHORT'),
                Joomla.Text._('AUGUST_SHORT'),
                Joomla.Text._('SEPTEMBER_SHORT'),
                Joomla.Text._('OCTOBER_SHORT'),
                Joomla.Text._('NOVEMBER_SHORT'),
                Joomla.Text._('DECEMBER_SHORT'),
            ],
            longhand: [
                Joomla.Text._('JANUARY'),
                Joomla.Text._('FEBRUARY'),
                Joomla.Text._('MARCH'),
                Joomla.Text._('APRIL'),
                Joomla.Text._('MAY'),
                Joomla.Text._('JUNE'),
                Joomla.Text._('JULY'),
                Joomla.Text._('AUGUST'),
                Joomla.Text._('SEPTEMBER'),
                Joomla.Text._('OCTOBER'),
                Joomla.Text._('NOVEMBER'),
                Joomla.Text._('DECEMBER'),
            ],
        },
        firstDayOfWeek: typeof config.firstday != 'undefined' ? config.firstday : 1,
        ordinal: function () {
            return "";
        },
        rangeSeparator: ' ' + Joomla.Text._('MOD_JFILTERS_FILTER_DATE_RANGE_SEPARATOR') +  ' ',
        weekAbbreviation: Joomla.Text._('JLIB_HTML_BEHAVIOR_WK'),
        amPM: [Joomla.Text._('JLIB_HTML_BEHAVIOR_AM'), Joomla.Text._('JLIB_HTML_BEHAVIOR_PM')]
    };
    flatpickr.l10ns.jl = JoomlaLocale;
    flatpickr.localize(JoomlaLocale);
}

JFilters.calendar.setActiveDates = (flatpickrInstance, activeDates, dayElement, showCounter = true) => {
    const dateFormatted = flatpickrInstance.formatDate(dayElement.dateObj, 'Y-m-d');
    if(activeDates) {
        activeDates.forEach((activeDate) => {
            if(activeDate.value == dateFormatted) {
                if(typeof activeDate.counter != 'undefined' && parseInt(activeDate.counter) > 0) {
                    dayElement.classList.add('jfilters-filter-calendar__date--active');
                    if(showCounter) {
                        dayElement.innerHTML += `<span class="jfilters-filter-calendar__date-counter" role="note" aria-label="${Joomla.Text._('MOD_JFILTERS_NUMBER_OF_ITEMS')}">${activeDate.counter}</span>`;
                    }
                }
            }
        })
    }
}

JFilters.calendar.boot = () => {
    const filterParams = Joomla.getOptions('jfilters.filter'); // Return early
    if (typeof filterParams !== 'undefined' && typeof flatpickr != 'undefined') {
        JFilters.calendar.localize({});
        filterParams.forEach(fltr => {
            if (!fltr.extraProperties || fltr.extraProperties.type != 'calendar') {
                // Continue to the next one
                return;
            }
            let filterConfig = fltr.extraProperties;
            const moduleSelector = window.JFilters.filteringModule.moduleWrapperSelectorPrefix + fltr.moduleId;
            const moduleElement = document.querySelector(moduleSelector);
            if(!moduleElement) {
                return;
            }
            let calendarWrapperSelector = filterConfig.wrapperSelector ? filterConfig.wrapperSelector : '.jfilters-filter-calendar';
            const calendarWrappers = [].slice.call(moduleElement.querySelectorAll(calendarWrapperSelector));
            let config = {
                filterId: fltr.id,
                moduleId: fltr.moduleId,
                filterAlias: filterConfig.alias,
                baseUrl: filterConfig.baseUrl,
                urlFormat: filterConfig.urlFormat,
                mode: filterConfig.calendarMode,
                wrap : true,
                altInput: true,
                altFormat : filterConfig.visualFormat ? filterConfig.visualFormat : 'd M, Y',
                showCounter: filterConfig.showCounter == "1" ? true : false,
                minDate: filterConfig.minDate,
                maxDate: filterConfig.maxDate,
                weekNumbers: filterConfig.weekNumbers == "1" ? true : false,
                enableTime: filterConfig.enableTime == "1" ? true : false,
                time_24hr: filterConfig.time_24hr == "1" ? true : false,
            };

            calendarWrappers.forEach((calendarWrapper) => {
                config.onChange = function(selectedDates, dateStr, instance) {
                    JFilters.calendar.update(instance, dateStr);
                }
                config.onDayCreate = function(dObj, dStr, fp, dayElem){
                    const activeDates = filterConfig.values;
                    JFilters.calendar.setActiveDates(fp, activeDates, dayElem, config.showCounter);
                }
                flatpickr(calendarWrapper, config);
            })
        });
    }
}

document.addEventListener("DOMContentLoaded", JFilters.calendar.boot);