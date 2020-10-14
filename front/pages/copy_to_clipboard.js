/* eslint-disable no-new */

import Clipboard from 'clipboard';

export default () => {
    if (Clipboard.isSupported()) {
        findAll(document, '.copy-link-button').forEach((element) => {
            $(element).show();
            const elemId = $(element).attr('id');

            new Clipboard(`#${elemId}`);
        });
    }
};
