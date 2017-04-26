export default class VoteLocationForm {
    constructor(api, country, postalCode, city, cityName, office) {
        this._api = api;
        this._country = country;
        this._postalCode = postalCode;
        this._city = city;
        this._cityName = cityName;
        this._office = office;

        this._initialCity = city.value;
        this._initialCityName = cityName.value;
        this._defaultInput = cityName;

        this._state = {
            country: country.value,
            postalCode: postalCode.value.replace(' ', ''),
            cities: null,
            consulates: null,
            loading: false
        };
    }

    prepare() {
        this.resetCity();
        this.resetSelect();
        this.loadConsulates();
        this.loadCities();
    }

    attachEvents() {
        const resetCityAndRefresh = () => {
            this._state.cities = null;
            this._state.consulates = null;

            this.loadCities();
            this.loadConsulates();
            this.refresh();
        };

        on(this._country, 'change', (event) => {
            this._state.country = event.target.value;
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

        if ('FR' !== this._state.country) {
            hide(dom('#vote-label-city-name'));
            show(dom('#vote-label-consulate'));

            this.displayConsulateSelector();
            return;
        }

        hide(dom('#vote-label-consulate'));
        show(dom('#vote-label-city-name'));

        this.displayVoteCitySelector();
    }

    displayConsulateSelector() {
        show(this._cityName);
        this._cityName.required = true;
        this._postalCode.value = '';
        this._office.value = '';
        this._city.value = '';

        if (!this._state.consulates || 0 === this._state.consulates.length) {
            this._cityName.value = this._initialCityName;
            return;
        }

        this.replaceCityNameInputWithConsulateSelector();

        for (let key in this._state.consulates) {
            const value = this._state.consulates[key];
            const option = this.createOption(value, value);

            if (value === this._initialCity) {
                option.selected = true;
            }

            this._cityName.appendChild(option);
        }

        this._cityName.disabled = false;
    }

    displayVoteCitySelector() {
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
        }

        this._city.disabled = false;
    }

    resetCity() {
        this.replaceCityNameInputWith(this._defaultInput);

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

    loadConsulates() {
        if (!this._state.loading && 'FR' !== this._state.country) {
            this._state.loading = true;
            addClass(this._cityName, 'address__postal-code--loading');

            this._api.getCountryConsulates(this._state.country, (consulates) => {
                this._state.consulates = consulates;
                this._state.loading = false;
                removeClass(this._cityName, 'address__postal-code--loading');
                this.refresh();
            });
        } else {
            this._state.loading = false;
            removeClass(this._cityName, 'address__postal-code--loading');
        }
    }

    createOption(value, label) {
        const opt = document.createElement('option');
        opt.value = value;
        opt.innerHTML = label;

        return opt;
    }

    replaceCityNameInputWithConsulateSelector() {
        const select = document.createElement('select');
        select.name = this._cityName.name;
        select.id = this._cityName.id;

        addClass(select, 'form');
        addClass(select, 'form--full');
        addClass(select, 'form__field');

        this.replaceCityNameInputWith(select);
    }

    replaceCityNameInputWith(element) {
        if (this._cityName === element) {
            return;
        }

        insertAfter(this._cityName, element);
        remove(this._cityName);
        this._cityName = element;
    }
}
