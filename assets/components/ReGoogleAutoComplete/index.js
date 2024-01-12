import { initAutocomplete, initGoogleServiceAutoComplete } from './googlePlacesAutocomplete';

/**
 * Alpine Component for ReSelect
 * @param {{
 *     apiKey: string,
 *     associatedFieldsPrefix: string,
 *     autocompleteInputId: string,
 * }} props
 *
 * @returns {AlpineComponent}
 */
const xReGoogleAutoComplete = (props) => ({
    init() {
        initAutocomplete(props);
        return initGoogleServiceAutoComplete(props);
    },
});

export default {
    xReGoogleAutoComplete,
};
