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
 * @param {google.maps.GeocoderAddressComponent[]} components
 */
export function fillInAddress(components) {
    // Get the place details from the autocomplete object.
    let address1 = '';
    let postcode = '';

    const cityInput = document.querySelector('#membership_request_address_cityName');
    const addressInput = document.querySelector('#membership_request_address_address');
    const postcodeInput = document.querySelector('#membership_request_address_postalCode');

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
            cityInput.value = component.long_name;
            break;
        case 'administrative_area_level_1': {
            break;
        }
        case 'country':
            break;
        }

        addressInput.value = address1;
        postcodeInput.value = postcode;

        const inputs = [addressInput, postcodeInput, cityInput];

        inputs.forEach((input) => {
            input.dispatchEvent(new Event('change'));
        });
    }
}
