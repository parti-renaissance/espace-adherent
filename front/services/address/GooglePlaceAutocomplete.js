import EventEmitter from 'events';

export default class GooglePlaceAutocomplete extends EventEmitter {
    constructor(wrapper, address, inputClassNames = '', disabled = false) {
        super();
        this._wrapper = wrapper;
        this._address = address;
        this._inputClassNames = inputClassNames;
        this._disabled = disabled;
        this._placeChanged = false;
        this._previousInputValue = null;
        this._interval = null;

        this.resetState();
    }

    build() {
        this.createAutocomplete();
        this.attachEvents();
    }

    createAutocomplete() {
        this.createInputElement();

        this._autocomplete = new google.maps.places.Autocomplete(this._input, { types: ['address'] });
        this._autocomplete.setFields(['address_components']);

        // Avoid form submit action when `Enter` (13) key is pressed in autocomplete select
        google.maps.event.addDomListener(this._input, 'keydown', (event) => {
            if (13 === event.keyCode) {
                event.preventDefault();
            }
        });
    }

    attachEvents() {
        this._autocomplete.addListener('place_changed', () => this.placeChangeHandle());

        /*
         * if the user has stopped typing text (address)
         * and Google Api do not provide any result
         * then emit `no_result` event
         */
        const step = 500; // 0.5 s
        let countOfCheck = 1;

        on(this._input, 'keypress', () => {
            if (null === this._interval) {
                this._placeChanged = false;

                this._interval = setInterval(() => {
                    if (this._previousInputValue !== this._input.value) {
                        this._previousInputValue = this._input.value;
                        countOfCheck = 1;
                        return;
                    }

                    if (2 >= ++countOfCheck) {
                        return;
                    }

                    /*
                     * If user has chosen one of the proposed by Google,
                     * then we stop the current check
                     */
                    if (this._placeChanged) {
                        clearInterval(this._interval);
                        this._interval = null;
                        return;
                    }

                    const container = dom('.pac-container');

                    if (null === container || false === container.hasChildNodes()) {
                        clearInterval(this._interval);
                        this._interval = null;
                        this.emit('no_result');
                    } else {
                        countOfCheck = 1;
                    }
                }, step);
            }
        });
    }

    placeChangeHandle() {
        this._placeChanged = true;

        const place = this._autocomplete.getPlace();

        if (place && place.address_components) {
            this.resetState();
            this.updateState(place.address_components);
        } else {
            this.updateStateWithInput();
        }

        this.updateFields();
    }

    updateState(addressComponents) {
        addressComponents.forEach((component) => {
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
            || (this._state.sublocality_level_1 && this._state.sublocality_level_1.long_name)
            || (this._state.postal_town && this._state.postal_town.long_name)
            || ''
        );
        this._address.setPostalCode(
            (this._state.postal_code && this._state.postal_code.long_name)
            || (this._state.postal_code_prefix && this._state.postal_code_prefix.long_name)
            || ''
        );

        if (this._state.country && this._state.country.short_name) {
            this._address.setCountry(this._state.country.short_name);

            if ('FR' !== this._state.country.short_name) {
                this._address.setRegion(
                    this._state.administrative_area_level_1 && this._state.administrative_area_level_1.long_name || ''
                );
            }
        } else if (!this._address.getCountry()) {
            this._address.setCountry('FR');
        }

        this.emit('changed');
    }

    resetState() {
        this._state = {
            street_number: null,
            route: null,
            locality: null,
            postal_town: null,
            sublocality_level_1: null,
            postal_code: null,
            postal_code_prefix: null,
            country: null,
            administrative_area_level_1: null,
        };
    }

    createInputElement() {
        this._input = document.createElement('input');
        this._input.placeholder = 'Adresse postale';
        this._input.className = this._inputClassNames;
        if (this._disabled) {
            this._input.disabled = 'disabled';
        }

        this._wrapper.appendChild(this._input);

        /**
         * Hack: replace HTML attribute `autocomplete="off"` added by Google API by `autocomplete="nope"`
         * The value `nope` is one hack too, invalid value disable the behaviour,
         * because `off` don't do it correctly in Chrome
         *
         * @link https://stackoverflow.com/a/49161445/6071674
         * @link https://developer.mozilla.org/en-US/docs/Web/Security/Securing_your_site/Turning_off_form_autocompletion
         */
        const observerHack = new MutationObserver((elements) => {
            observerHack.disconnect();
            elements.shift().target.autocomplete = 'nope';
        });

        observerHack.observe(this._input, {
            attributes: true,
            attributeFilter: ['autocomplete'],
        });
    }

    setInputElementValue() {
        this._input.value = this._address.getFullAddress();
    }

    updateStateWithInput() {
        this._state.route = { long_name: this._input.value };
    }
}
