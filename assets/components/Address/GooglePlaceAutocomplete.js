export default class GooglePlaceAutocomplete {
    constructor({ wrapper, addressForm }) {
        this._wrapper = wrapper;
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
        this.configureAutocompleteInput();

        this._autocomplete = new google.maps.places.Autocomplete(this._input, {
            types: ['address'],
        });
        this._autocomplete.setFields(['address_components']);
        this._input.value = this._addressForm ? this._addressForm.getAddressString() : '';
    }

    configureAutocompleteInput() {
        this._input = findOne(this._wrapper, 'input');
    }

    bindListeners() {
        if (this._addressForm) {
            this._autocomplete.addListener('place_changed', () => {
                const place = this._autocomplete.getPlace();

                if (place.address_components !== undefined) {
                    this._addressForm.updateWithPlace(this._autocomplete.getPlace());
                    this._input.value = this._addressForm.getAddressString();
                }
            });
        }
    }
}
