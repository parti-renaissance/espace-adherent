export default class VoteOfficeForm {
    constructor(api, country, voteOffice) {
        this._api = api;
        this._country = country;
        this._voteOffice = voteOffice;

        this._initialVoteOffice = this._voteOffice.value;
        this._defaultInput = voteOffice;

        this._state = {
            country: this._country.value,
            voteOffices: null,
            loading: false
        };
    }

    prepare() {
        this.loadVoteOffices();
    }

    attachEvents() {
        const resetVoteOfficeAndRefresh = () => {
            this._state.voteOffices = null;
            this.loadVoteOffices();
            this.refresh();
        };

        on(this._country, 'change', (event) => {
            this._state.country = event.target.value;
            resetVoteOfficeAndRefresh();
        });
    }

    refresh() {
        if ('FR' === this._state.country) {
            this.replaceInputWith(this._defaultInput);
            return;
        }

        this.replaceInputByASelect();
        if (!this._state.voteOffices || 0 === this._state.voteOffices.length) {
            if (!this._state.loading) { // unknown vote office for this country
                this.replaceInputWith(this._defaultInput);
                return;
            }
            this._voteOffice.disabled = true;
        }
        for (let key in this._state.voteOffices) {
            const value = this._state.voteOffices[key];
            const option = this.createOption(value, value);

            if (value === this._initialVoteOffice) {
                option.selected = true;
            }

            this._voteOffice.appendChild(option);
            this._voteOffice.disabled = false;
        }
    }

    resetVoteOffice() {
        this._voteOffice.defaultValue = '';
        this._voteOffice.value = '';
        this._voteOffice.required = false;
    }

    replaceInputByASelect() {
        const select = document.createElement('select');
        select.name = this._voteOffice.name;
        select.id = this._voteOffice.id;

        addClass(select, 'form');
        addClass(select, 'form--full');
        addClass(select, 'form__field');

        this.replaceInputWith(select);
    }

    loadVoteOffices() {
        if (!this._state.loading && 'FR' !== this._state.country) {
            this._state.loading = true;

            this._api.getVoteOffices(this._state.country, (voteOfficies) => {
                this._state.voteOffices = voteOfficies;
                this._state.loading = false;
                this.refresh();
            });
        } else {
            this._state.loading = false;
        }
    }

    createOption(value, label) {
        const opt = document.createElement('option');
        opt.value = value;
        opt.innerHTML = label;

        return opt;
    }

    replaceInputWith(element) {
        insertAfter(this._voteOffice, element);
        remove(this._voteOffice);
        this._voteOffice = element;
    }
}
