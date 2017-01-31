import AddressForm from './AddressForm';

export default class AddressFormFactory {
    constructor(api) {
        this._api = api;
    }

    createAddressForm(country, postalCode, city, cityName) {
        return new AddressForm(
            this._api,
            dom('#'+country),
            dom('#'+postalCode),
            dom('#'+city),
            dom('#'+cityName)
        );
    }
}
