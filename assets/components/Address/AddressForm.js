export default class AddressForm {
    constructor({
        address, postalCode, cityName, country,
    }) {
        this._address = address;
        this._postalCode = postalCode;
        this._cityName = cityName;
        this._country = country;
    }

    showFields() {
        this._address.required = 'false' !== this._address.dataset.required;
        this._postalCode.required = 'false' !== this._postalCode.dataset.required;
        this._cityName.required = 'false' !== this._cityName.dataset.required;
        this._country.required = 'false' !== this._country.dataset.required;
    }

    hideFields() {
        this._address.required = false;
        this._postalCode.required = false;
        this._cityName.required = false;
        this._country.required = false;
    }

    getAddressString() {
        return [
            this._address.value,
            this._postalCode.value,
            this._cityName.value,
            Number.isInteger(this._country.selectedIndex) ? this._country.options[this._country.selectedIndex].innerHTML : '',
        ].filter((item) => item).join(', ');
    }

    reset() {
        this._address.value = '';
        this._postalCode.value = '';
        this._cityName.value = '';
        this._country.value = '';
    }

    updateWithPlace({ address_components: addressComponents }) {
        this.reset();

        const placeData = {
            street_number: null,
            route: null,
            locality: null,
            postal_town: null,
            sublocality_level_1: null,
            sublocality_level_2: null,
            sublocality_level_3: null,
            postal_code: null,
            postal_code_prefix: null,
            plus_code: null,
            country: null,
            administrative_area_level_1: null,
        };

        addressComponents.forEach((component) => {
            const type = component.types[0];
            if (type in placeData) {
                placeData[type] = component;
            }
        });

        let addressValue = [
            ((placeData.street_number && placeData.street_number.long_name) || ''),
            ((placeData.route && placeData.route.long_name) || ''),
        ].join(' ').trim();

        if (0 === addressValue.length) {
            addressValue = [
                ((placeData.sublocality_level_3 && placeData.sublocality_level_3.long_name) || ''),
                ((placeData.sublocality_level_2 && placeData.sublocality_level_2.long_name) || ''),
                ((placeData.sublocality_level_1 && placeData.sublocality_level_1.long_name) || ''),
            ].filter((el) => null != el && '' !== el).join(', ').trim();
        }

        this._address.value = addressValue;

        this._cityName.value = (
            (placeData.locality && placeData.locality.long_name)
            || (placeData.sublocality_level_1 && placeData.sublocality_level_1.long_name)
            || (placeData.postal_town && placeData.postal_town.long_name)
            || ''
        );

        this._postalCode.value = (
            (placeData.postal_code && placeData.postal_code.long_name)
            || (placeData.postal_code_prefix && placeData.postal_code_prefix.long_name)
            || (placeData.plus_code && placeData.plus_code.long_name)
            || ''
        );

        this._country.value = placeData.country.short_name || 'FR';
    }
}
