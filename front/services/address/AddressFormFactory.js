import AddressForm from './AddressForm';

export default class AddressFormFactory {
    constructor(api) {
        this._api = api;
    }

    createAddressForm(country, postalCode, city, cityName, cityNameRequired, region = null) {
        let regionElem = null;
        if(region !== null && dom('#'+region) !== 'undefined') {
            regionElem = dom('#'+region);
        }
        return new AddressForm(
            this._api,
            dom('#'+country),
            dom('#'+postalCode),
            dom('#'+city),
            dom('#'+cityName),
            cityNameRequired,
            regionElem,
        );
    }
}
