import Cookies from 'js-cookie';
import reqwest from 'reqwest';

import ShareDialogFactory from './services/sharer/ShareDialogFactory';
import Sharer from './services/sharer/Sharer';

/**
 * @param {Container} di
 */
export default (di) => {

    /*
     * Reqwest (AJAX library)
     * https://github.com/ded/reqwest
     */
    di.set('reqwest', () => {
        return reqwest;
    });

    /*
     * Cookies library
     * https://github.com/js-cookie/js-cookie
     */
    di.set('cookies', () => {
        return Cookies;
    });

    /*
     * Sharer
     */
    di.set('sharer', () => {
        return new Sharer(di.get('sharer.dialog_factory'));
    });

    di.set('sharer.dialog_factory', () => {
        return new ShareDialogFactory();
    });

};
