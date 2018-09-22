import {
    CATEGORIES,
} from '../actions/citizen-projects';

const INTITIAL_STATE = {
    categories: [],
    error: false,
    loading: false,
};

export default function categoriesReducer(state = INTITIAL_STATE, action) {
    switch (action.type) {
    case `${CATEGORIES}_PENDING`:
        return {
            ...state,
            loading: true,
            error: false,
        };
    case `${CATEGORIES}_FULFILLED`:
        return {
            ...state,
            categories: action.payload,
            loading: false,
            error: false,
        };
    case `${CATEGORIES}_REJECTED`:
        return {
            ...state,
            loading: false,
            error: true,
        };

    default:
        return state;
    }
}
