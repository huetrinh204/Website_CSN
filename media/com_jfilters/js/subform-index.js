/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2023 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

((Joomla, document) => {
    if (!Joomla) {
        throw new Error('core.js was not properly initialised');
    }

    // top namespace
    window.JFilters = window.JFilters || {};

    if (!Joomla) {
        throw new Error('core.js was not properly initialised');
    }

    JFilters.subform_index = () => {
        let totalItems = null;
        let offset = 0;
        let progress = null;
        let getRequest;
        const path = 'index.php?option=com_jfilters&view=subform&tmpl=component&format=json';
        const token = `&${document.getElementById('jfilters-index-token').getAttribute('name')}=1`;

        const removeElement = id => {
            const element = document.getElementById(id);

            if (element) {
                return element.parentNode.removeChild(element);
            }

            return null;
        };

        /**
         * Update the progress bar.
         *
         * @param header
         * @param message
         * @param complete
         */
        const updateProgress = (header, message, complete) => {
            progress = offset / totalItems * 100;
            const progressBar = document.getElementById('progress-bar');
            const progressHeader = document.getElementById('jfilters-progress-header');
            const progressMessage = document.getElementById('jfilters-progress-message');

            if (progressHeader && header) {
                progressHeader.innerText = header;
            }

            if (progressMessage && message) {
                console.log(message);
                progressMessage.innerHTML = Joomla.sanitizeHtml(message);
            }

            if (progressBar) {
                if (progress < 100) {
                    progressBar.style.width = `${progress}%`;
                    progressBar.setAttribute('aria-valuenow', progress);
                } else {
                    progressBar.classList.remove('bar-success');
                    progressBar.classList.add('bar-warning');
                    progressBar.setAttribute('aria-valuemin', 100);
                    progressBar.setAttribute('aria-valuemax', 200);
                    progressBar.style.width = `${progress}%`;
                    progressBar.setAttribute('aria-valuenow', progress);
                }

                // Auto close the window
                if (complete) {
                    removeElement('progress');
                    window.parent.Joomla.Modal.getCurrent().close();
                }
            }
        };
        /**
         * Handle the success response.
         *
         * @param json
         */
        let handleSuccess = function (json) {

            try {
                if (json === null) {
                    throw new Error(resp);
                }

                if (json.error) {
                    throw new Error(json);
                }
                if (json.start) {
                    // eslint-disable-next-line prefer-destructuring
                    totalItems = json.total;
                }

                offset = json.batchOffset;
                console.log('offset:',offset,' batch:',json.batchSize, ' total:', totalItems, ' complete:', json.complete);
                updateProgress(json.header, json.msg, json.complete);

                // Index the next batch
                if (offset < totalItems) {
                    getRequest('subform.batchIndex');
                }

            } catch(error) {
                throw new Error(error);
            }
        }

        /**
         * Handle the failure response.
         *
         * @param xhr
         */
        const handleFailure = xhr => {
            const progressHeader = document.getElementById('jfilters-progress-header');
            const message = document.getElementById('jfilters-progress-message');
            let data = typeof xhr === 'object' && xhr.responseText ? xhr.responseText : null;
            data = data ? JSON.parse(data) : null;

            if (progressHeader) {
                progressHeader.innerText = data.header;
                progressHeader.classList.add('jfilters-index-error');
            }

            if (message && data.error) {
                message.innerHTML = Joomla.sanitizeHtml(data.msg);
                message.classList.add('jfilters-index-error');
            }
        }

        getRequest = task => {
            Joomla.request({
                url: `${path}&task=${task}${token}`,
                method: 'GET',
                data: '',
                perform: true,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                onSuccess: response => {
                    handleSuccess(JSON.parse(response));
                },
                onError: xhr => {
                    handleFailure(xhr);
                }
            });
        };

        const initialize = () => {
            getRequest('subform.startIndexer');
        };

        initialize();
    };
})(Joomla, document);

document.addEventListener('DOMContentLoaded', () => {
    window.Subform_Indexer = JFilters.subform_index();
});
