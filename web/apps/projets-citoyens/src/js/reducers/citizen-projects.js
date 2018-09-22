import {
    CITIZEN_PROJECTS,
    FILTERED_ITEM,
    LOAD_MORE,
} from '../actions/citizen-projects';

const INTITIAL_STATE = {
    projects: [],
    moreItems: true,
    error: false,
    loading: null,
    loadingMore: false,
    filter: {
      name: '',
      category: null,
      city: null,
      page: 1,
      page_size: 6,
      country: 'FR',
      filterPending: false,
    }
};

export default function citizenProjectReducer(state = INTITIAL_STATE, action) {
    let { metadata } = (action.payload || {});
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
            moreItems: metadata.current_page !== metadata.last_page,
            loading: false,
            error: false,
        };
    case `${CITIZEN_PROJECTS}_REJECTED`:
        return {
            ...state,
            loading: false,
            error: true,
        };

    case `${LOAD_MORE}_PENDING`:
        return {
            ...state,
            loadingMore: true,
            error: false,
        };
    case `${LOAD_MORE}_FULFILLED`:
        return {
            ...state,
            filter: {
              ...state.filter,
              page: metadata.current_page,
            },
            projects: [
              ...state.projects,
              ...action.payload.items
            ],
            moreItems: metadata.current_page !== metadata.last_page,
            loadingMore: false,
            error: false,
        };
    case `${LOAD_MORE}_REJECTED`:
        return {
            ...state,
            loading: false,
            error: true,
        };

    case `${FILTERED_ITEM}`:
        return {
            ...state,
            filter: {
              ...state.filter,
              ...action.payload,
            }
        };

    default:
        return { ...state };
    }
}
