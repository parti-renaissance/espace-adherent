export default class AddressForm {
    constructor(api, country, postalCode, city, cityName) {
        this._api = api;
        this._country = country;
        this._postalCode = postalCode;
        this._city = city;
        this._cityName = cityName;

        this._initialCity = this._city.value;
        this._initialCityName = this._cityName.value;

        this._state = {
            country: this._country.value,
            postalCode: this._postalCode.value,
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
        on(this._country, 'change', (event) => {
            this._state.country = event.target.value;
            this._state.cities = null;
            this.loadCities();
            this.refresh();
        });

        on(this._postalCode, 'keyup', (event) => {
            if (event.keyCode !== 8 && (event.keyCode < 48 || event.keyCode > 57)) {
                // Ignore non alpha-numeric characters
                return;
            }

            this._state.postalCode = event.target.value;
            this._state.cities = null;
            this.loadCities();
            this.refresh();
        });
    }

    refresh() {
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
}
