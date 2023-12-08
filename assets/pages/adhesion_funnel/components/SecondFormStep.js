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
        lastName: false,
        firstName: false,
        nationality: true,
        country: false,
        address: false,
        postalCode: false,
        cityName: false,
    },

    async handleOnSubmit(e) {
        if (!this._handleOnSubmitBase(e)) {
            const autocomplete = dom('#membership_request_address_autocomplete');
            const addressFormValidity = ['country', 'address', 'postalCode', 'cityName'].every((x) => true === this.fieldsValid[x]);
            if (!addressFormValidity && !autocomplete.value) {
                this.showAutoComplete = false;
            }
            return;
        }
        this.handleNextStep();
    },

});

export const isFranceCountry = () => {
    const countryInput = document.querySelector('#membership_request_address_country');
    return 'FR' !== countryInput.value;
};

export default SecondForm;
