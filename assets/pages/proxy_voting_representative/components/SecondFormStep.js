/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import '../../../components/Validator/typedef';
import CommonFormStep from './CommonFormStep';

/** @typedef {{label:string, value:string}} Option */

/**
 * First Step component for funnel
 * @param {{ zoneApi:string }} props
 * @return {AlpineComponent}
 */
const SecondForm = (props) => ({
    ...CommonFormStep(),
    nextStepId: 'step_3',
    id: 'step_2',
    showAutoComplete: true,
    votePlaceUuid: null,
    isNotInFrance: false,
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
            'input[id^="procuration_proxy__"]'
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
        return fetch(`${props.zoneApi}?q=${query}&types[]=city&types[]=borough&types[]=country`)
            .then((response) => response.json())
            .then((data) => data.filter((x) => !('city' === x.type && 1 < x.postal_code.length))
                .map((x) => ({
                    label: `${x.name}`,
                    value: x.uuid,
                })));
    },

    handleVoteZoneChange(uuid) {
        console.log(uuid, 'uuid');
        this.votePlaceUuid = uuid;
    },
    /**
     * @param {string} query
     * @return {Promise<Option>}
     */
    getVotePlace($dispatch, query) {
        console.log(query);
        const uuid = this.votePlaceUuid;
        const el = document.querySelector('#procuration_proxy_votePlace_select_widget');
        if (!uuid) {
            document.dispatchEvent(new CustomEvent('x-validate:procuration_proxy_votePlace', {
                detail: {
                    status: 'error',
                    message: 'Veuillez sélectionner une zone de vote',
                },
            }));
            return Promise.resolve([]);
        }
        return fetch(`${props.zoneApi}?q=${query}&types[]=vote_place&parent_zone=${this.votePlaceUuid}`)
            .then((response) => response.json())
            .then((data) => data.filter((x) => !('city' === x.type && 1 < x.postal_code.length))
                .map((x) => ({
                    label: `${x.name}`,
                    value: x.uuid,
                })));
    },

    async handleOnSubmit(e) {
        if (!this.checkFormValidity(e)) return;
        this.handleNextStep();
    },

});

export const isFranceCountry = () => {
    const countryInput = document.querySelector('#procuration_proxy_country');
    return 'FR' !== countryInput.value;
};

export default SecondForm;
