import EventEmitter from 'events';

export default class GooglePlaceAutocomplete extends EventEmitter {
    constructor({ wrapper, addressForm, inputClassName }) {
        super();
        this._wrapper = wrapper;
        this._addressForm = addressForm;
        this._inputClassName = inputClassName;
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
        this.createInputElement();

        this._autocomplete = new google.maps.places.Autocomplete(this._input, { types: ['address'] });
        this._autocomplete.setFields(['address_components']);
        this._input.value = this._addressForm ? this._addressForm.getAddressString() : '';
    }

    createInputElement() {
        this._input = document.createElement('input');
        this._input.placeholder = '';
        this._input.className = `outline-0 ${this._inputClassName}`;

        this._wrapper.appendChild(this._input);
    }

    bindListeners() {
        if (this._addressForm) {
            this._autocomplete.addListener('place_changed', () => {
                this._addressForm.updateWithPlace(this._autocomplete.getPlace());

                this.emit('changed');
            });
        }
    }
}
