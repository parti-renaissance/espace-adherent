import { Loader } from '@googlemaps/js-api-loader';

/**
 * @typedef {'address' | 'postalCode' | 'cityName' | 'country' | 'autocomplete'} FieldsName
 */

/**
 * @typedef {Record<FieldsName, HTMLInputElement>} AssociatedFields
 */

/** @typedef {{
 *      autocompleteService: google.maps.places.AutocompleteService,
 *      placesService: google.maps.places.PlacesService,
 *      sessionToken: ReturnType<google.maps.places.AutocompleteSessionToken>
 *  }} GoogleServices
 */

/** @typedef {
 *     'street_number' |
 *     'route' |
 *     'locality' |
 *     'postal_town' |
 *     'sublocality_level_1' |
 *     'sublocality_level_2' |
 *     'sublocality_level_3' |
 *     'postal_code' |
 *     'postal_code_prefix' |
 *     'plus_code' |
 *     'country' |
 *     'administrative_area_level_1'
 * } GoogleComponentsTypesKeys
 */

/** @typedef {Record<GoogleComponentsTypesKeys, google.maps.GeocoderAddressComponent | null>} PlaceData */

/**
 * Initialize google places
 * @param {google.maps.PlacesLibrary} google
 * @returns {GoogleServices}
 */
function initPlaces(google) {
    return {
        autocompleteService: new google.AutocompleteService(),
        placesService: new google.PlacesService(document.createElement('div')),
        sessionToken: new google.AutocompleteSessionToken(),
    };
}

function refreshToken() {
    if (window.googleServices) {
        window.googleServices.sessionToken = new google.maps.places.AutocompleteSessionToken();
    }
}

/**
 * Wrapper to configure autocompleteService and getPlacePredictions
 * @param {GoogleServices} googleServices
 */
function getPlacePredictions(googleServices) {
    return (query) =>
        googleServices.autocompleteService
            .getPlacePredictions({
                input: query,
                sessionToken: googleServices.sessionToken,
                types: ['address'],
            })
            .then(({ predictions }) => predictions)
            .then((predictions) =>
                predictions.map((prediction) => ({
                    label: prediction.description,
                    value: prediction.place_id,
                }))
            )
            .catch(() => []);
}

/**
 * @param {{
 *     associatedFieldsPrefix: string,
 *     autocompleteInputId: string,
 * }} props
 * @return {AssociatedFields}
 */
function getAssociatedFields(props) {
    /** @type {FieldsName[]} */
    const fieldsName = ['address', 'postalCode', 'cityName', 'country', 'autocomplete'];

    /**
     * @param {Partial<AssociatedFields>} acc
     * @param {FieldsName} fieldName
     * @return {Partial<AssociatedFields>}
     */
    const handler = (acc, fieldName) => {
        /** @type {HTMLInputElement} */
        const element = document.querySelector(`#${props.associatedFieldsPrefix}_${fieldName}`);
        if (!element) {
            throw new Error(`Element not found for selector #${props.associatedFieldsPrefix}_${fieldName}`);
        }
        acc[fieldName] = element;
        return acc;
    };
    return fieldsName.reduce(handler, /** @type Partial<AssociatedFields> */ {});
}

/**
 * @param {{
 *     associatedFieldsPrefix: string,
 *     autocompleteInputId: string,
 * }} props
 * @return {AssociatedFields}
 */
export function clearAssociatedFields(props) {
    const { autocomplete, ...fields } = getAssociatedFields(props);
    Object.values(fields).forEach((input) => {
        input.value = '';
        input.dispatchEvent(new Event('change'));
    });
}

/**
 * @param {{
 *     associatedFieldsPrefix: string,
 *     autocompleteInputId: string,
 * }} props
 */
function initAutocomplete(props) {
    const { autocomplete: autocompleteInput, ...fields } = getAssociatedFields(props);
    const fullAddress = Object.values(fields)
        .map((el) => el.value)
        .join(' ')
        .trim();

    if (!fullAddress) return;
    autocompleteInput.value = 'prefilled';
    window[`options_${autocompleteInput.id}`] = [
        {
            label: fullAddress,
            value: 'prefilled',
        },
    ];
}

/**
 * @return {Record<GoogleComponentsTypesKeys, null>}
 */
const initPlaceData = () => ({
    street_number: null,
    route: null,
    locality: null,
    postal_town: null,
    sublocality_level_1: null,
    sublocality_level_2: null,
    sublocality_level_3: null,
    postal_code: null,
    postal_code_prefix: null,
    plus_code: null,
    country: null,
    administrative_area_level_1: null,
});

/**
 * @param {PlaceData}  placeData
 * @return {string}
 */
const getCityName = (placeData) =>
    (placeData.locality && placeData.locality.long_name) ||
    (placeData.sublocality_level_1 && placeData.sublocality_level_1.long_name) ||
    (placeData.postal_town && placeData.postal_town.long_name) ||
    '';

/**
 * @param {PlaceData}  placeData
 * @return {string}
 */
const getPostalCode = (placeData) =>
    (placeData.postal_code && placeData.postal_code.long_name) ||
    (placeData.postal_code_prefix && placeData.postal_code_prefix.long_name) ||
    (placeData.plus_code && placeData.plus_code.long_name) ||
    '';

/**
 * @param {PlaceData}  placeData
 * @return {string}
 */
const getAddressValue = (placeData) =>
    [(placeData.street_number && placeData.street_number.long_name) || '', (placeData.route && placeData.route.long_name) || ''].join(' ').trim() ||
    [
        (placeData.sublocality_level_3 && placeData.sublocality_level_3.long_name) || '',
        (placeData.sublocality_level_2 && placeData.sublocality_level_2.long_name) || '',
        (placeData.sublocality_level_1 && placeData.sublocality_level_1.long_name) || '',
    ]
        .filter((el) => null != el && '' !== el)
        .join(', ')
        .trim();

/**
 * @param {{
 *     associatedFieldsPrefix: string,
 *     autocompleteInputId: string,
 *     components: google.maps.GeocoderAddressComponent[],
 * }} props
 * @return {void}
 */
export function fillInAddress({ components, ...props }) {
    clearAssociatedFields(props);
    // Get the place details from the autocomplete object.
    const { autocomplete, ...fields } = getAssociatedFields(props);

    const placeData = initPlaceData();

    components.forEach((component) => {
        const type = component.types[0];
        if (type in placeData) {
            placeData[type] = component;
        }
    });

    fields.address.value = getAddressValue(placeData);
    fields.cityName.value = getCityName(placeData);
    fields.postalCode.value = getPostalCode(placeData);

    if (placeData.country && placeData.country.short_name) {
        window.dispatchEvent(
            new CustomEvent(`x-inject-option:${props.associatedFieldsPrefix}_country`, {
                detail: {
                    label: placeData.country.long_name,
                    value: placeData.country.short_name,
                },
            })
        );
    } else if (!fields.country.value) {
        fields.country.value = 'FR';
    }

    Object.values(fields).forEach((input) => {
        input.dispatchEvent(new Event('change'));
    });
}

let lastDetail = null;

/**
 * @param {{
 *     apiKey: string,
 *     associatedFieldsPrefix: string,
 *     autocompleteInputId: string,
 *     services: GoogleServices
 * }} props
 */
function watchAutocompleteInput(props) {
    document.addEventListener(`autocomplete_change:${props.autocompleteInputId}`, ({ detail }) => {
        if (detail && (lastDetail === detail || 'prefilled' === detail)) {
            return;
        }
        if (!detail) {
            clearAssociatedFields(props);
            return;
        }

        props.services.placesService.getDetails(
            {
                placeId: detail,
                sessionToken: props.services.sessionToken,
                fields: ['address_components'],
            },
            (place, status) => {
                if ('OK' === status) {
                    fillInAddress({
                        ...props,
                        components: place.address_components,
                    });
                } else {
                    document.dispatchEvent(
                        new CustomEvent(`x-validate:${props.autocompleteInputId}`, {
                            detail: {
                                status,
                                message: "Une erreur est survenue lors de la récupération de l'adresse",
                            },
                        })
                    );
                }
                lastDetail = detail;
                refreshToken();
            }
        );
    });
}

/**
 * @param {{
 *     apiKey: string,
 *     associatedFieldsPrefix: string,
 *     autocompleteInputId: string,
 * }} props
 */
async function initGoogleServiceAutoComplete(props) {
    initAutocomplete(props);
    if (!window.googleServices) {
        const loaderInstance = new Loader({
            apiKey: props.apiKey,
            version: 'weekly',
            libraries: ['places'],
        });

        await loaderInstance
            .importLibrary('places')
            .then((x) => initPlaces(x))
            .then((services) => {
                window.googleServices = services;
            });
    }

    if (!window.getPlacePredictions) {
        window.getPlacePredictions = getPlacePredictions(window.googleServices);
    }

    watchAutocompleteInput({
        ...props,
        services: window.googleServices,
    });

    window.queryGooglePlaces = (input) => {
        if (window.getPlacePredictions) {
            return window.getPlacePredictions(input);
        }
        return Promise.resolve([]);
    };
}

export { initGoogleServiceAutoComplete, initAutocomplete };
