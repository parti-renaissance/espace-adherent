import ShareDialogFactory from './services/sharer/ShareDialogFactory';
import Sharer from './services/sharer/Sharer';
import TaxReturnProvider from './services/donation/TaxReturnProvider';
import ReqwestApiClient from './services/api/ReqwestApiClient';
import AddressFormFactory from './services/address/AddressFormFactory';
import Slugifier from './services/slugifier/Slugifier';

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
     * Slugifier
     */
    di.set('slugifier', () => {
        return new Slugifier();
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

    /*
     * API
     */
    di.set('api', () => {
        return new ReqwestApiClient(di.get('reqwest'));
    });

    /*
     * Donation
     */
    di.set('donation.tax_return_provider', () => {
        return new TaxReturnProvider();
    });

    /*
     * Address form
     */
    di.set('address.form_factory', () => {
        return new AddressFormFactory(di.get('api'));
    });

};
