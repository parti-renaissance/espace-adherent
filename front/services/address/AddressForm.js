export default class AddressForm {
    constructor(api, country, postalCode, city, cityName, options = {}) {
        this._api = api;
        this._country = country;
        this._postalCode = postalCode;
        this._city = city;
        this._cityName = cityName;
        this._options = options;

        this._initialCity = this._city.value;
        this._initialCityName = this._cityName.value;

        this._autocomplete = null;

        this._state = {
            country: this._country.value,
            postalCode: this._postalCode.value.replace(' ', ''),
            cities: null,
            loading: false,
        };
    }

    prepare() {
        if (this.isCityAutocompleteMode()) {
            this.initCityAutocomplete();
        } else {
            this.resetCity();
            this.resetSelect();
            this.loadCities();
        }
    }

    initCityAutocomplete() {
        if (null !== this._autocomplete) {
            return;
        }

        const options = {
            types: ['(cities)'],
        };

        if (this._state.country) {
            options.componentRestrictions = {
                country: this._state.country,
            };
        }

        if ('undefined' !== typeof google) {
            this._autocomplete = new google.maps.places.Autocomplete(this._cityName, options);
        } else {
            console.error('Google lib is undefined');
        }
    }

    attachEvents() {
        const resetCityAndRefresh = () => {
            this._state.cities = null;
            this.loadCities();
            this.refresh();
        };

        const updateAutocomplete = () => {
            this._autocomplete.setComponentRestrictions({
                country: this._state.country,
            });
        };

        on(this._country, 'change', (event) => {
            this._state.country = event.target.value;
            if (this._autocomplete) {
                updateAutocomplete();
            } else {
                resetCityAndRefresh();
            }
        });

        on(this._postalCode, 'input', (event) => {
            this._state.postalCode = event.target.value.replace(' ', '');
            resetCityAndRefresh();
        });

        if (this._autocomplete) {
            this._autocomplete.addListener('place_changed', () => {
                this._cityName.value = this._autocomplete.getPlace().name;
            });
        }
    }

    refresh() {
        if (this.isCityAutocompleteMode()) {
            return;
        }

        this.resetSelect();
        this.resetCity();

        if ('FR' !== this._state.country) {
            show(this._cityName);
            this._cityName.required = true;
            this._cityName.value = this._initialCityName;
            return;
        }

        show(this._city);
        this._city.required = true;

        if (!this._state.cities || 0 === this._state.cities.length) {
            const defaultOption = this.createOption('', this._cityName.placeholder);
            defaultOption.selected = true;
            defaultOption.disabled = true;
            defaultOption.hidden = true;

            this._city.appendChild(defaultOption);
            this._city.disabled = true;
            return;
        }

        for (let inseeCode in this._state.cities) {
            const value = this._state.postalCode+'-'+inseeCode;
            const option = this.createOption(value, this._state.cities[inseeCode]);

            if (value === this._initialCity) {
                option.selected = true;
            }

            this._city.appendChild(option);
            this._city.disabled = false;
        }
    }

    resetCity() {
        hide(this._cityName);
        hide(this._city);

        this._cityName.defaultValue = '';
        this._cityName.value = '';
        this._cityName.required = false;
        this._city.defaultValue = '';
        this._city.value = '';
        this._city.required = false;
    }

    resetSelect() {
        const select = document.createElement('select');
        select.name = this._city.name;
        select.id = this._city.id;

        addClass(select, 'form');
        addClass(select, 'form--full');
        addClass(select, 'form__field');

        insertAfter(this._city, select);
        remove(this._city);

        this._city = select;
    }

    loadCities() {
        if (!this._state.loading && 'FR' === this._state.country && 5 === this._state.postalCode.length) {
            this._state.loading = true;
            addClass(this._postalCode, 'address__postal-code--loading');

            this._api.getPostalCodeCities(this._state.postalCode, (cities) => {
                this._state.cities = cities;
                this._state.loading = false;
                removeClass(this._postalCode, 'address__postal-code--loading');
                this.refresh();
            });
        } else {
            this._state.loading = false;
            removeClass(this._postalCode, 'address__postal-code--loading');
        }
    }

    createOption(value, label) {
        const opt = document.createElement('option');
        opt.value = value;
        opt.innerHTML = label;

        return opt;
    }

    isCityAutocompleteMode() {
        return this._options.hasOwnProperty('cityAutocomplete') && true === this._options.cityAutocomplete;
    }
}
