export default class AddressObject {
    constructor(addressField, postalCodeField, cityField, regionField, countryField) {
        this._address = addressField;
        this._postalCode = postalCodeField;
        this._city = cityField;
        this._region = regionField;
        this._country = countryField;

        this._addressOptions = AddressObject.getFieldOptions(addressField);
        this._postalCodeOptions = AddressObject.getFieldOptions(postalCodeField);
        this._cityOptions = AddressObject.getFieldOptions(cityField);
        this._countryOptions = AddressObject.getFieldOptions(countryField);

        if (this._region) {
            this._regionOptions = AddressObject.getFieldOptions(regionField);
        }
    }

    static getFieldOptions(field) {
        return {
            value: field.value,
            required: field.required,
        };
    }

    setAddress(address) {
        this._address.value = address;
    }

    setPostalCode(postalCode) {
        this._postalCode.value = postalCode;
    }

    setCity(city) {
        this._city.value = city;
    }

    setCountry(country) {
        this._country.value = country;
    }

    getCountry() {
        return this._country.value;
    }

    setRegion(region) {
        if(this._region !== null) {
            this._region.value = region;
        }
    }

    isFilled() {
        return this._address.value && this._postalCode.value && this._city.value && this._country.value;
    }

    setRequired(value) {
        this._address.required = value;
        this._postalCode.required = value;
        this._city.required = value;
        this._country.required = value;

        if (this._region !== null) {
            this._region.required = value;
        }
    }

    resetRequired() {
        this._address.required = this._addressOptions.required;
        this._postalCode.required = this._postalCodeOptions.required;
        this._city.required = this._cityOptions.required;
        this._country.required = this._countryOptions.required;

        if (this._region !== null) {
            this._region.required = this._regionOptions.required;
        }
    }
}
