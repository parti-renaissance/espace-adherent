/* eslint-disable no-new */

import Clipboard from 'clipboard';

export default () => {
    if (Clipboard.isSupported()) {
        show(dom('#copy-link-button'));

        new Clipboard('#copy-link-button');
    }
};
