export default class GooglePlaceAutocomplete {
    constructor(wrapper, addressField, cityField, zipCodeField, countryField, inputClassNames = '') {
        this._wrapper = wrapper;

        this._addressField = addressField;
        this._cityField = cityField;
        this._zipCodeField = zipCodeField;
        this._countryField = countryField;

        this._inputClassNames = inputClassNames;

        this.resetState();
    }

    build() {
        this.createAutocomplete();
        this.attachEvents();
    }

    createAutocomplete() {
        this._input = document.createElement('input');
        this._input.className = this._inputClassNames;
        this._autocomplete = new google.maps.places.Autocomplete(this._input, {types: ['address']});

        this._wrapper.appendChild(this._input);
    };

    attachEvents() {
        this._autocomplete.addListener('place_changed', this.placeChangeHandle.bind(this));
    }

    placeChangeHandle() {
        const place = this._autocomplete.getPlace();

        if (place.address_components) {
            this.resetState();
            this.updateState(place.address_components);
            this.updateFields();
        }
    }

    updateState(addressComponents) {
        addressComponents.forEach(function (component) {
            const type = component.types[0];
            if (type in this._state) {
                this._state[type] = component;
            }
        }, this);
    }

    updateFields() {
        this._addressField.value = [
            (this._state.street_number && this._state.street_number.long_name || ''),
            (this._state.route && this._state.route.long_name || ''),
        ].join(' ');

        this._cityField.value = (this._state.locality && this._state.locality.long_name || '');
        this._zipCodeField.value = (this._state.postal_code && this._state.postal_code.long_name || '');
        this._countryField.value = (this._state.country && this._state.country.short_name || '');
    }

    resetState() {
        this._state = {
            street_number: null,
            route: null,
            locality: null,
            postal_code: null,
            country: null,
        };
    }
}
