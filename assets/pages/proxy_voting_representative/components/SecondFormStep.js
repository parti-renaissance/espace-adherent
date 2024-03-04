/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import '../../../components/Validator/typedef';
import CommonFormStep from './CommonFormStep';

/** @typedef {{label:string, value:string}} Option */

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
        voteZone: false,
    },

    init() {
        const addressInputs = document.querySelectorAll(
            'input[id^="donation_request_address_"]:not(#membership_request_address_autocomplete)'
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

    /**
     * @param {string} query
     * @return {Promise<Option>}
     */
    getVoteZone(query) {
        return new Promise((resolve) => {
            setTimeout(resolve([
                {
                    label: 'France',
                    value: '5bfaea8c-835e-11eb-ba14-42010a84009d',
                },
                {
                    label: 'Belgique',
                    value: '5bfaea8c-835e-11eb-ba14-42010a84009d',
                },
            ]), 300);
        });
    },
    /**
     * @param {string} query
     * @return {Promise<Option>}
     */
    getVotePlace(query) {
        return new Promise((resolve) => {
            setTimeout(resolve([
                {
                    label: 'Bureau de vote 1',
                    value: 'cef5805f-b2d3-4d58-8c82-eeeb32164b8',
                },
                {
                    label: 'Bureau de vote 2',
                    value: 'cef5805f-b2d3-4d58-8c82-eeeb32164b8',
                },
            ]), 300);
        });
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
