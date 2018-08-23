import callApi from './../../utils/api';

// Action types
export const AUTOCOMPLETE_SEARCH = 'AUTOCOMPLETE_SEARCH';
export const FILTERED_ITEM = 'FILTERED_ITEM';

const API = process.env.REACT_APP_API_URL;

export function autocompleteSearch(type, value) {
    return {
        type: AUTOCOMPLETE_SEARCH,
        payload: callApi(API, `/api/referent/search/autocomplete?type=${type}&value=${value}`),
    };
}

export function setFilteredItem(value) {
    return {
        type: FILTERED_ITEM,
        payload: value,
    };
}
