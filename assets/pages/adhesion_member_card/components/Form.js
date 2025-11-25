/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * @returns {AlpineComponent}
 */
const Form = () => ({
    showAutoComplete: true,
    formId: 'member_card_address',
    fieldsValid: {
        country: false,
        address: false,
        postalCode: false,
        cityName: false,
    },

    init() {
        window.isFranceCountry = () => {
            const countryInput = document.querySelector(`#${this.formId}_country`);
            return 'FR' !== countryInput.value;
        };
        const addressInputs = document.querySelectorAll(`input[id^=${this.formId}_]:not(#${this.formId}_autocomplete)`);
        addressInputs.forEach((x) => {
            window.addEventListener(`x-validate:${x.id}`, ({ detail }) => {
                if ('error' === detail.status && this.showAutoComplete) {
                    this.showAutoComplete = false;
                }
            });
        });
    },

    setFieldValid(field) {
        return (value) => {
            this.fieldsValid[field] = value;
            return this.fieldsValid;
        };
    },

    isFormValid() {
        return Object.values(this.fieldsValid).every((x) => x);
    },

    checkFormValidity() {
        const addressFormValidity = ['country', 'address', 'postalCode', 'cityName'].every((x) => true === this.fieldsValid[x]);
        if (!addressFormValidity) {
            this.showAutoComplete = false;
            return false;
        }
        return true;
    },

    async handleOnSubmit(e) {
        e.preventDefault();
        if (!this.checkFormValidity()) return;
        const form = document.querySelector('form');
        form.submit();
    },
});

export default Form;
