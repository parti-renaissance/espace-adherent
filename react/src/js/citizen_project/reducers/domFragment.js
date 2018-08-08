import { GET_HEADER, GET_FOOTER } from './../actions/domFragment';

const defaultState = {
    headerFragment: [],
    footerFragment: [],
};

export default (state = defaultState, action) => {
    switch (action.type) {
    case `${GET_HEADER}`:
        return {
            ...state,
            headerFragment: action.payload,
        };
    case `${GET_FOOTER}`:
        return {
            ...state,
            headerFragment: action.payload,
        };

    default:
        return {
            ...state,
        };
    }
};
