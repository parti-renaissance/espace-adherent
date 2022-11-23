export default class AddressForm {
    constructor({
        address, postalCode, cityName, country,
    }) {
        this._address = address;
        this._postalCode = postalCode;
        this._cityName = cityName;
        this._country = country;
    }

    getAddressString() {
        return [
            this._address.value,
            this._cityName.value,
            this._postalCode.value,
            this._country.value,
        ].filter((item) => item).join(', ');
    }

    reset() {
        this._address.value = '';
        this._postalCode.value = '';
        this._cityName.value = '';
        this._country.value = '';
    }

    updateWithPlace({ address_components: placeData }) {
        this.reset();

        placeData.forEach((data) => {
            if (data.types.includes('street_number')) {
                this._address.value += data.long_name;
            } else if (data.types.includes('route')) {
                this._address.value += (this._address.value ? ' ' : '') + data.long_name;
            } else if (data.types.includes('locality') || data.types.includes('administrative_area_level_1')) {
                this._cityName.value = data.long_name;
            } else if (data.types.includes('country')) {
                this._country.value = data.short_name;
            } else if (data.types.includes('postal_code')) {
                this._postalCode.value = data.long_name;
            }
        });
    }
}
