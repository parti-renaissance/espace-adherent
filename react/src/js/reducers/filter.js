import { AUTOCOMPLETE_SEARCH, FILTERED_ITEM } from './../actions/filter';

const defaultState = {
    autocomplete: {
        committees: [],
        cities: [],
        countries: [],
    },
    filteredItem: '',
    autocompletePending: false,
};

export default (state = defaultState, action) => {
    switch (action.type) {
    case `${FILTERED_ITEM}`:
        return {
            ...state,
            filteredItem: action.payload,
        };
    case `${AUTOCOMPLETE_SEARCH}_PENDING`:
        return {
            ...state,
            autocompletePending: true,
        };
    case `${AUTOCOMPLETE_SEARCH}_FULFILLED`:
        return {
            ...state,
            autocompletePending: false,
            autocomplete: {
                ...state.autocomplete,
                ...action.payload,
            },
        };
    default:
        return {
            ...state,
        };
    }
};
