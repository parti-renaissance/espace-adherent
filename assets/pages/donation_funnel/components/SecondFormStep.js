/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import '../../../components/Validator/typedef';
import CommonFormStep from './CommonFormStep';

/**
 * First Step component for funnel
 * @return {AlpineComponent}
 */
const SecondForm = () => ({
    ...CommonFormStep(),
    nextStepId: 'step_3',
    id: 'step_2',
    showAutoComplete: true,
    fieldsValid: {
        gender: false,
        emailAddress: false,
        lastName: false,
        firstName: false,
        nationality: true,
        country: false,
        address: false,
        postalCode: false,
        cityName: false,
    },

    init() {
        const addressInputs = document.querySelectorAll('input[id^="donation_request_address_"]:not(#membership_request_address_autocomplete)');
        addressInputs.forEach((x) => {
            window.addEventListener(`x-validate:${x.id}`, ({ detail }) => {
                if ('error' === detail.status && this.showAutoComplete) {
                    this.showAutoComplete = false;
                }
            });
        });
    },

    checkFormValidity(e) {
        if (!this._handleOnSubmitBase(e)) {
            const addressFormValidity = ['country', 'address', 'postalCode', 'cityName'].every((x) => true === this.fieldsValid[x]);
            if (!addressFormValidity) {
                this.showAutoComplete = false;
            }
            return false;
        }
        return true;
    },

    async handleOnSubmit(e) {
        if (!this.checkFormValidity(e)) return;
        this.handleNextStep();
    },
});

export const isFranceCountry = () => {
    const countryInput = document.querySelector('#donation_request_address_country');
    return 'FR' !== countryInput.value;
};

export default SecondForm;
