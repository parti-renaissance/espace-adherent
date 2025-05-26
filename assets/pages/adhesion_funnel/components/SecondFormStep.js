/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import '../../../components/Validator/typedef';
import CommonFormStep from './CommonFormStep';

import { handlePostAccountResponse, postAccount } from '../shared/utils';

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

    init() {
        const addressInputs = document.querySelectorAll(
            'input[id^="membership_request_address_"]:not(#membership_request_address_autocomplete)'
        );
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
        this.setStepData(['address', 'phone']);
        if (this.isReContribution) {
            this.loading = true;
            const bodyPayload = {
                ...this.formData,
                exclusiveMembership: true,
                isPhysicalPerson: true,
            };
            await postAccount(bodyPayload)
                .then((res) => handlePostAccountResponse.call(this, res, (payload) => {
                    this.stepToFill = 3;
                    this.nextStepId = 'step_4';
                    this.handleNextStep();
                    this.clearLocalStorage();
                }, {
                    error: 'Une erreur est survenue lors de la modification de votre compte',
                }));
        }
        this.handleNextStep();
    },

});

export const isFranceCountry = () => {
    const countryInput = document.querySelector('#membership_request_address_country');
    return 'FR' !== countryInput.value;
};

export default SecondForm;
