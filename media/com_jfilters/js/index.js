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

    JFilters.index = () => {
        let getRequest;
        const path = 'index.php?option=com_jfilters&view=index&tmpl=component&format=json';
        const indexerPath = 'index.php?option=com_finder&view=indexer&tmpl=component';
        const token = `&${document.getElementById('jfilters-index-token').getAttribute('name')}=1`;

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
                else {
                    const spinner = document.getElementById('jfilters-index-spinner');
                    const message = document.getElementById('jfilters-index-message');
                    spinner.remove();
                    message.innerHTML = '<div class="alert alert-success">'+Joomla.sanitizeHtml(json.message)+'</div>';
                    // Set a small delay, to read the message.
                    setTimeout(function(){window.location.href=`${indexerPath}`}, 2000);
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
            const message = document.getElementById('jfilters-index-message');
            let data = typeof xhr === 'object' && xhr.responseText ? xhr.responseText : null;
            data = data ? JSON.parse(data) : null;

            if (message && data.error) {
                message.innerHTML = Joomla.sanitizeHtml(data.message);
                message.classList.add('alert alert-danger');
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
            getRequest('index.delete');
        };

        initialize();
    };
})(Joomla, document);

document.addEventListener('DOMContentLoaded', () => {
    window.Indexer = JFilters.index();
});
