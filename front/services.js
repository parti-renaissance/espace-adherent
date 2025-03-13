import ShareDialogFactory from './services/sharer/ShareDialogFactory';
import Sharer from './services/sharer/Sharer';
import ReqwestApiClient from './services/api/ReqwestApiClient';
import AddressFormFactory from './services/address/AddressFormFactory';
import DateFieldsSynchronizer from './services/form/DateFieldsSynchronizer';

/**
 * @param {Container} di
 */
export default (di) => {
    /*
     * Reqwest (AJAX library)
     * https://github.com/ded/reqwest
     */
    di.set('reqwest', () => reqwest);

    /*
     * Cookies library
     * https://github.com/js-cookie/js-cookie
     */
    di.set('cookies', () => Cookies);

    /*
     * Sharer
     */
    di.set('sharer', () => new Sharer(di.get('sharer.dialog_factory')));

    di.set('sharer.dialog_factory', () => new ShareDialogFactory());

    /*
     * API
     */
    di.set('api', () => new ReqwestApiClient(di.get('reqwest')));

    /*
     * Address form
     */
    di.set('address.form_factory', () => new AddressFormFactory(di.get('api')));
    di.set('form.date_synchronizer', () => new DateFieldsSynchronizer());
};
