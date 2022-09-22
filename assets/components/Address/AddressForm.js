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
            this._address?.value,
            this._cityName?.value,
            this._postalCode?.value,
            this._country?.value,
        ].filter((item) => item).join(', ');
    }

    setValue(element, value) {
        if (element) {
            element.value = value;
        }
    }

    reset() {
        this.setValue(this._address, '');
        this.setValue(this._postalCode, '');
        this.setValue(this._cityName, '');
        this.setValue(this._country, '');
    }

    updateWithPlace({ address_components: placeData }) {
        this.reset();

        placeData.forEach((data) => {
            if (data.types.includes('street_number')) {
                this.setValue(this._address, (this._address ? this._address.value : '') + data.long_name);
            } else if (data.types.includes('route')) {
                this.setValue(this._address, (this._address ? this._address.value + ' ' : '') + data.long_name);
            } else if (data.types.includes('locality')) {
                this.setValue(this._cityName, data.long_name);
            } else if (data.types.includes('country')) {
                this.setValue(this._country, data.short_name);
            } else if (data.types.includes('postal_code')) {
                this.setValue(this._postalCode, data.long_name);
            }
        });
    }
}
