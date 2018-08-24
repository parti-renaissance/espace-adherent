import { PROJECT_FILTER, FILTERED_ITEM } from './../actions/filter';

const defaultState = {
    keyword: '',
    category: null,
    city: null,
    country: 'FR',
    filterPending: false,
};

export default (state = defaultState, action) => {
    switch (action.type) {
    case `${FILTERED_ITEM}`:
        return {
            ...state,
            ...action.payload,
        };
    case `${PROJECT_FILTER}_PENDING`:
        return {
            ...state,
            filterPending: true,
        };
    case `${PROJECT_FILTER}_FULFILLED`:
        return {
            ...state,
            filterPending: false,
        };
    default:
        return {
            ...state,
        };
    }
};
