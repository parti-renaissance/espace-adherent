import callApi from './../../utils/api';

// Action types
export const AUTOCOMPLETE_SEARCH = 'AUTOCOMPLETE_SEARCH';
export const FILTERED_ITEM = 'FILTERED_ITEM';

export function autocompleteSearch(type, value) {
    return {
        type: AUTOCOMPLETE_SEARCH,
        payload: callApi(`/api/referent/search/autocomplete?type=${type}&value=${value}`),
    };
}

export function setFilteredItem(value) {
    return {
        type: FILTERED_ITEM,
        payload: value,
    };
}
