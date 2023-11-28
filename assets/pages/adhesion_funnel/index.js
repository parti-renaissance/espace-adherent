import { Loader } from '@googlemaps/js-api-loader';
import FirstFormStep from './components/FirstFormStep';
import SecondFormStep from './components/SecondFormStep';
import * as GooglePlaces from './google_places';

/**
 * @param {string} googleMapApiKey
 */
export default (googleMapApiKey) => {
    window.Alpine.data('FirstFormStep', FirstFormStep);
    window.Alpine.data('SecondFormStep', SecondFormStep);

    const loaderInstance = new Loader({
        apiKey: googleMapApiKey,
        version: 'weekly',
        libraries: ['places'],
    });

    loaderInstance.importLibrary('places')
        .then((x) => GooglePlaces.initPlaces(x))
        .then((services) => {
            window.getPlacePredictions = GooglePlaces.getPlacePredictions(services);
            document.addEventListener('autocomplete_change:membership_request_address_autocomplete', ({ detail }) => {
                services.placesService.getDetails({
                    placeId: detail,
                    sessionToken: services.sessionToken,
                    fields: ['address_components'],
                }, (place, status) => {
                    if ('OK' === status) {
                        GooglePlaces.fillInAddress(place.address_components);
                    } else {
                        document.dispatchEvent(new CustomEvent('x-validate:membership_request_address_autocomplete', {
                            detail: {
                                status,
                                message: 'Une erreur est survenue lors de la récupération de l\'adresse',
                            },
                        }));
                    }
                });
            });
        });

    window.queryGooglePlaces = (input) => {
        if (window.getPlacePredictions) {
            return window.getPlacePredictions(input);
        }
        return Promise.resolve([]);
    };
};
