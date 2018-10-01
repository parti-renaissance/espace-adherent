import { GET_MARKUP } from './../actions/dom';

const defaultState = {
    header: [],
    footer: [],
    loading: false,
    error: false
};

export default (state = defaultState, action) => {
    switch (action.type) {
    case `${GET_MARKUP}_PENDING`:
        return {
            ...state,
            loading: true,
        };
    case `${GET_MARKUP}_FULFILLED`:
        return {
          ...action.payload,
          loading: false
        };
    case `${GET_MARKUP}_REJECTED`:
        return {
          ...state,
          loading: false,
          error: true,
        };

    default:
        return {
            ...state,
        };
    }
};
