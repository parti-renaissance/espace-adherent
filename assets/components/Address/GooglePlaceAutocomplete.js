export default class GooglePlaceAutocomplete {
    constructor({ element, addressForm }) {
        this._input = element;
        this._addressForm = addressForm;
    }

    build() {
        // Stop if google class is undefined
        if ('undefined' === typeof google) {
            throw new Error('Google Place class is undefined');
        }

        this.createAutocomplete();
        this.bindListeners();
    }

    createAutocomplete() {
        this._autocomplete = new google.maps.places.Autocomplete(this._input, { types: ['address'] });
        this._autocomplete.setFields(['address_components']);
        this.updateSearchValue(this._addressForm ? this._addressForm.getAddressString() : '');
    }

    updateSearchValue(value) {
        this._input.value = value;
    }

    bindListeners() {
        if (this._addressForm) {
            this._autocomplete.addListener('place_changed', () => {
                this._addressForm.updateWithPlace(this._autocomplete.getPlace());
                if (this._addressForm && this._addressForm.getAddressString()) {
                    this.updateSearchValue(this._addressForm.getAddressString());
                }
            });
        }
    }
}
