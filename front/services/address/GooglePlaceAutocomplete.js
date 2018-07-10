import EventEmitter from 'events';

export default class GooglePlaceAutocomplete extends EventEmitter {
    constructor(wrapper, address, inputClassNames = '') {
        super();
        this._wrapper = wrapper;
        this._address = address;
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
        this._input.placeholder = 'Adresse postale';
        this._autocomplete = new google.maps.places.Autocomplete(this._input, {types: ['address']});

        this._autocomplete.setFields(['address_components']);

        this._wrapper.appendChild(this._input);

        // Avoid form submit action when `Enter` (13) key is pressed in autocomplete select
        google.maps.event.addDomListener(this._input, 'keydown', (event) => {
            if (event.keyCode === 13) {
                event.preventDefault();
            }
        });
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
        this._address.setAddress([
            (this._state.street_number && this._state.street_number.long_name || ''),
            (this._state.route && this._state.route.long_name || ''),
        ].join(' '));

        this._address.setCity(
            (this._state.locality && this._state.locality.long_name)
            || (this._state.administrative_area_level_1 && this._state.administrative_area_level_1.long_name)
            || ''
        );
        this._address.setPostalCode(this._state.postal_code && this._state.postal_code.long_name || '');
        this._address.setCountry(this._state.country && this._state.country.short_name || '');

        this.emit('changed');
    }

    resetState() {
        this._state = {
            street_number: null,
            route: null,
            locality: null,
            administrative_area_level_1: null,
            postal_code: null,
            country: null,
        };
    }
}
