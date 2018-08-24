import {
    CITIZEN_PROJECTS,
} from '../actions/citizen-projects';

const INTITIAL_STATE = {
    projects: [],
    count: null,
    error: false,
    loading: null,
};

export default function citizenProjectReducer(state = INTITIAL_STATE, action) {
    switch (action.type) {
    case `${CITIZEN_PROJECTS}_PENDING`:
        return {
            ...state,
            loading: true,
            error: false,
        };
    case `${CITIZEN_PROJECTS}_FULFILLED`:
        return {
            ...state,
            projects: action.payload.items,
            count: action.payload.metadata.total_items,
            loading: false,
            error: false,
        };
    case `${CITIZEN_PROJECTS}_REJECTED`:
        return {
            ...state,
            loading: false,
            error: true,
        };

    default:
        return state;
    }
}
