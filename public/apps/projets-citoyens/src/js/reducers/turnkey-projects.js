import {
    PINNED,
    TURNKEY_PROJECTS,
    TURNKEY_DETAIL,
} from '../actions/turnkey-projects';

const INITIAL_STATE = {
    pinned: {
        loading: false,
        error: false,
        project: null,
    },
    all: {
        loading: false,
        error: false,
        projects: [],
    },
    detail: {
        loading: false,
        error: false,
        project: null,
    },
};

export default function turnkeyProjectReducer(state = INITIAL_STATE, action) {
    switch (action.type) {
    case `${PINNED}_PENDING`:
        return {
            ...state,
            pinned: {
                ...state.pinned,
                loading: true,
                error: false,
            },
        };
    case `${PINNED}_FULFILLED`:
        return {
            ...state,
            pinned: {
                project: action.payload,
                loading: false,
                error: false,
            },
        };
    case `${PINNED}_REJECTED`:
        return {
            ...state,
            pinned: {
                ...state.pinned,
                loading: false,
                error: true,
            },
        };

    case `${TURNKEY_PROJECTS}_PENDING`:
        return {
            ...state,
            all: {
                ...state.all,
                loading: true,
                error: false,
            },
        };
    case `${TURNKEY_PROJECTS}_FULFILLED`:
        return {
            ...state,
            all: {
                projects: action.payload,
                loading: false,
                error: false,
            },
        };
    case `${TURNKEY_PROJECTS}_REJECTED`:
        return {
            ...state,
            all: {
                ...state.all,
                loading: false,
                error: true,
            },
        };

    case `${TURNKEY_DETAIL}_PENDING`:
        return {
            ...state,
            detail: {
                ...state.detail,
                loading: true,
                error: false,
            },
        };
    case `${TURNKEY_DETAIL}_FULFILLED`:
        return {
            ...state,
            detail: {
                project: action.payload,
                loading: false,
                error: false,
            },
        };
    case `${TURNKEY_DETAIL}_REJECTED`:
        return {
            ...state,
            detail: {
                ...state.detail,
                loading: false,
                error: true,
            },
        };

    default:
        return state;
    }
}
