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
    showManualBDV: false,
    votePlaceUuid: null,
    votePlaceLoading: false,
    isVotePlacesEmpty: false,
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
        const addressInputs = document.querySelectorAll('input[id^="procuration_"]');
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

    /**
     * @param {string} query
     * @return {Promise<Option>}
     */
    getVoteZone(query) {
        const boroughCodeCityToExclude = ['75056', '69123', '13055', 'FR']; // Paris, Lyon, Marseille, France
        return fetch(`${props.zoneApi}?q=${query}&types[]=city&types[]=borough&types[]=country`)
            .then((response) => response.json())
            .then((data) =>
                data
                    .filter((x) => !boroughCodeCityToExclude.includes(x.code))
                    .map((x) => ({
                        label: `${x.name} | ${'country' === x.type ? x.code : x.postal_code}`,
                        value: `${x.uuid}__${x.type}__${x.code}`,
                    }))
            );
    },

    /**
     * @param {boolean} open
     */
    setManualBDV(open) {
        this.showManualBDV = open;
    },

    handleVoteZoneChange(uuidType) {
        if (!uuidType) {
            this.votePlaceUuid = null;
            this.isVotePlacesEmpty = true;
            return;
        }

        const proxyOrRequest = document.querySelector('[id^=procuration_proxy_]') ? 'proxy' : 'request';
        const DOM_TOM_CODES = ['GP', 'GF', 'MQ', 'YT', 'NC', 'PF', 'BL', 'MF', 'SX', 'PM', 'WF', 'RE'];
        const [uuid, type, code] = uuidType.split('__');
        this.isNotInFrance = 'country' === type && !DOM_TOM_CODES.includes(code) && 'FR' !== code;
        document.querySelector('[id$=_voteZone]').value = uuid;
        this.votePlaceUuid = null;
        this.votePlaceLoading = true;
        this.getVotePlace(uuid)
            .then((options) => {
                window[`options_procuration_${proxyOrRequest}_votePlace`] = options;
                this.isVotePlacesEmpty = 0 === options.length;
                if (1 === options.length) {
                    setTimeout(() => {
                        this.$dispatch(`x-inject-option:procuration_${proxyOrRequest}_votePlace`.toLowerCase(), options[0]);
                    }, 0);
                }

                this.votePlaceUuid = uuid;
            })
            .finally(() => {
                this.votePlaceLoading = false;
            });
    },
    /**
     * @param {string} uuid
     * @return {Promise<Option>}
     */
    getVotePlace(uuid) {
        return fetch(`${props.zoneApi}?noLimit&types[]=vote_place&parent_zone=${uuid}&searchEvenEmptyTerm=true`)
            .then((response) => response.json())
            .then((data) =>
                data.map((x) => ({
                    label: `${x.name}`,
                    value: x.uuid,
                }))
            );
    },

    async handleOnSubmit(e) {
        if (!this.checkFormValidity(e)) return;
        this.handleNextStep();
    },
});

export const isFranceCountry = () => {
    const countryInput = document.querySelector('[id$=_country]');
    return 'FR' !== countryInput.value;
};

export default SecondForm;
