/** @typedef {{
 * autocompleteService: google.maps.places.AutocompleteService,
 * placesService: google.maps.places.PlacesService,
 * sessionToken: ReturnType<google.maps.places.AutocompleteSessionToken>
 *  }} GoogleServices
 */

/**
 * Initialize google places
 * @param {google.maps.PlacesLibrary} google
 * @returns {GoogleServices}
 */
export function initPlaces(google) {
    return {
        autocompleteService: new google.AutocompleteService(),
        placesService: new google.PlacesService(document.createElement('div')),
        sessionToken: new google.AutocompleteSessionToken(),
    };
}

/**
 * Wrapper to configure autocompleteService and getPlacePredictions
 * @param {GoogleServices} googleServices
 */
export function getPlacePredictions(googleServices) {
    return (query) => googleServices.autocompleteService.getPlacePredictions({
        input: query,
        sessionToken: googleServices.sessionToken,
        types: ['address'],
    })
        .then(({ predictions }) => predictions)
        .then((predictions) => predictions.map((prediction) => ({
            label: prediction.description,
            value: prediction.place_id,
        })))
        .catch(() => []);
}

/**
 * @return {{country: Element, address: Element, cityName: Element, autocomplete: Element, postalCode: Element}}
 */
const getAssociatedFields = () => ({
    address: document.querySelector('#membership_request_address_address'),
    postalCode: document.querySelector('#membership_request_address_postalCode'),
    cityName: document.querySelector('#membership_request_address_cityName'),
    country: document.querySelector('#membership_request_address_country'),
    autocomplete: document.querySelector('#membership_request_address_autocomplete'),
});

export function clearAssociatedFields() {
    const {
        autocomplete,
        ...fields
    } = getAssociatedFields();
    Object.values(fields)
        .forEach((input) => {
            input.value = '';
            input.dispatchEvent(new Event('change'));
        });
}

export function initAutocomplete() {
    const {
        autocomplete: autocompleteInput,
        ...fields
    } = getAssociatedFields();
    const fullAddress = Object.values(fields)
        .map((el) => el.value)
        .join(' ')
        .trim();

    if (!fullAddress) return;
    autocompleteInput.value = 'prefilled';
    window[`options_${autocompleteInput.id}`] = [{
        label: fullAddress,
        value: 'prefilled',
    }];
}

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

const getCityName = (placeData) => (
    (placeData.locality && placeData.locality.long_name)
    || (placeData.sublocality_level_1 && placeData.sublocality_level_1.long_name)
    || (placeData.postal_town && placeData.postal_town.long_name)
    || ''
);

const getPostalCode = (placeData) => (
    (placeData.postal_code && placeData.postal_code.long_name)
    || (placeData.postal_code_prefix && placeData.postal_code_prefix.long_name)
    || (placeData.plus_code && placeData.plus_code.long_name)
    || ''
);

const getAddressValue = (placeData) => [
    ((placeData.street_number && placeData.street_number.long_name) || ''),
    ((placeData.route && placeData.route.long_name) || '')].join(' ')
    .trim()
    || [((placeData.sublocality_level_3 && placeData.sublocality_level_3.long_name) || ''),
        ((placeData.sublocality_level_2 && placeData.sublocality_level_2.long_name) || ''),
        ((placeData.sublocality_level_1 && placeData.sublocality_level_1.long_name) || ''),
    ].filter((el) => null != el && '' !== el)
        .join(', ')
        .trim();

/**
 * @param {google.maps.GeocoderAddressComponent[]} components
 */
export function fillInAddress(components) {
    clearAssociatedFields();
    // Get the place details from the autocomplete object.
    const {
        autocomplete,
        ...fields
    } = getAssociatedFields();

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
        window.dispatchEvent(new CustomEvent('x-inject-option:membership_request_address_country', {
            detail: {
                label: placeData.country.long_name,
                value: placeData.country.short_name,
            },
        }));
    } else if (!fields.country.value) {
        fields.country.value = 'FR';
    }

    Object.values(fields)
        .forEach((input) => {
            input.dispatchEvent(new Event('change'));
        });
}
