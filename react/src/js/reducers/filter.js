import { COMMITTEE_SEARCH, COMMITTEE_FILTER } from './../actions';

const defaultState = {
    committeeFilter: '',
    committeeSearched: [],
};

export const filter = (state = defaultState, action) => {
    switch (action.type) {
    case COMMITTEE_FILTER:
        return {
            ...state,
            committeeFilter: action.value,
        };
    case COMMITTEE_SEARCH:
        return {
            ...state,
            committeeSearched: action.value,
        };
    default:
        return {
            ...state,
        };
    }
};
