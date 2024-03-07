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
                    value: `${x.uuid}__${x.type}`,
                })));
    },

    // getVoteZone: debounce(this._getVoteZone, 300),

    handleVoteZoneChange(uuidType) {
        const [uuid, type] = uuidType.split('__');
        this.isNotInFrance = 'country' === type;
        // document.getElementById('procuration_proxy_votePlace').value = uuid;
        this.votePlaceUuid = null;
        this.getVotePlace(uuid)
            .then((options) => {
                this.votePlaceUuid = uuid;
                window.options_procuration_proxy_votePlace = options;
            });
    },
    /**
     * @param {string} uuid
     * @return {Promise<Option>}
     */
    getVotePlace(uuid) {
        return fetch(`${props.zoneApi}?noLimit&types[]=vote_place&parent_zone=${uuid}`)
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
