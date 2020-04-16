export default class AddressForm {
    constructor(api, country, postalCode, city, cityName, cityNameRequired, region = null) {
        this._api = api;
        this._country = country;
        this._postalCode = postalCode;
        this._city = city;
        this._cityName = cityName;
        this._region = region;

        this._initialCity = this._city.value;
        this._initialCityName = this._cityName.value;

        this._cityNameRequired = typeof cityNameRequired === 'undefined' ? true : cityNameRequired;

        this._state = {
            country: this._country.value,
            postalCode: this._postalCode.value.replace(' ', ''),
            cities: null,
            loading: false
        };
    }

    prepare() {
        this.resetCity();
        this.resetSelect();
        this.loadCities();
    }

    attachEvents() {
        const resetCityAndRefresh = () => {
            this._state.cities = null;
            this.loadCities();
            this.refresh();
        };

        on(this._country, 'change', (event) => {
            this._state.country = event.target.value;

            if (event.detail) {
                if (event.detail.zipCode) {
                    this._state.postalCode = event.detail.zipCode.replace(' ', '');
                }

                if (event.detail.cityName) {
                    this._initialCityName = event.detail.cityName;
                }
            }

            resetCityAndRefresh();
        });

        on(this._postalCode, 'input', (event) => {
            this._state.postalCode = event.target.value.replace(' ', '');
            resetCityAndRefresh();
        });
    }

    refresh() {
        this.resetSelect();
        this.resetCity();

        // Display City name field if the country is not FR or the zip code is unknown
        if ('FR' !== this._state.country || Array.isArray(this._state.cities) && 0 === this._state.cities.length) {
            show(this._cityName);
            if (this._region !== null) {
                show(this._region);
            }
            this._cityName.required = this._cityNameRequired;
            this._cityName.value = this._initialCityName;
            return;
        }

        if (this._region !== null) {
            hide(this._region);
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
        addClass(select, 'em-form__field');

        insertAfter(this._cityName, select);
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
}
