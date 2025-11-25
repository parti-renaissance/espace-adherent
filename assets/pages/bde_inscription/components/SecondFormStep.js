/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import '../../../components/Validator/typedef';
import CommonFormStep from './CommonFormStep';

/** @typedef {{label:string, value:string}} Option */

/**
 * Second Step component for funnel
 * @return {AlpineComponent}
 */
const SecondForm = () => ({
    ...CommonFormStep(),
    nextStepId: 'step_3',
    id: 'step_2',
    showAutoComplete: true,
    fieldsValid: {
        civility: false,
        lastName: false,
        firstName: false,
        country: false,
        address: false,
        postalCode: false,
        cityName: false,
    },

    init() {
        const addressInputs = document.querySelectorAll('input[id^="inscription_request_"]');
        addressInputs.forEach((x) => {
            window.addEventListener(`x-validate:${x.id.toLowerCase()}`, ({ detail }) => {
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
        if (!this.checkFormValidity(e)) {
            return;
        }

        this.handleNextStep();
    },
});

export const isFranceCountry = () => {
    const countryInput = document.querySelector('[id$=_country]');
    return 'FR' !== countryInput.value;
};

export default SecondForm;
