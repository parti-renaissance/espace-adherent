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

/**
 * @param {google.maps.GeocoderAddressComponent[]} components
 */
export function fillInAddress(components) {
    clearAssociatedFields();
    // Get the place details from the autocomplete object.
    let address1 = '';
    let postcode = '';

    const {
        autocomplete,
        ...fields
    } = getAssociatedFields();

    // eslint-disable-next-line no-restricted-syntax
    for (const component of components) {
        // @ts-ignore remove once typings fixed
        const componentType = component.types[0];

        switch (componentType) {
        case 'street_number': {
            address1 = `${component.long_name} ${address1}`;
            break;
        }

        case 'route': {
            address1 += component.short_name;
            break;
        }

        case 'postal_code': {
            postcode = `${component.long_name}${postcode}`;
            break;
        }

        case 'postal_code_suffix': {
            postcode = `${postcode}-${component.long_name}`;
            break;
        }
        case 'locality':
            fields.cityName.value = component.long_name;
            break;
        case 'administrative_area_level_1': {
            break;
        }
        case 'country':
            window.dispatchEvent(new CustomEvent('x-inject-option:membership_request_address_country', {
                detail: {
                    label: component.long_name,
                    value: component.short_name,
                },
            }));
            break;
        }

        fields.address.value = address1;
        fields.postalCode.value = postcode;

        Object.values(fields)
            .forEach((input) => {
                input.dispatchEvent(new Event('change'));
            });
    }
}
