import { RESET_LOADING } from '../constants/actionTypes';

const initialState = {};

const loadingReducer = (state = initialState, action) => {
    const { type, payload } = action;

    if (type === RESET_LOADING) {
        return initialState;
    }

    const matches = /(.*)_(REQUEST|SUCCESS|FAILURE)/.exec(type);

    // not a *_REQUEST / *_SUCCESS /  *_FAILURE actions, so we ignore them
    if (!matches) return state;

    const [, requestName, requestState] = matches;
    return {
        ...state,
        // Store whether a request is happening at the moment or not
        // e.g. will be true when receiving GET_TODOS_REQUEST
        //      and false when receiving GET_TODOS_SUCCESS / GET_TODOS_FAILURE
        [`${requestName}${payload.id ? `_${payload.id}` : ''}`]: {
            isFetching: 'REQUEST' === requestState,
            isSuccess: 'SUCCESS' === requestState,
            isError: 'FAILURE' === requestState,
        },
    };
};

export default loadingReducer;

export const getLoadingState = (state, requestName, requestState) =>
    state[requestName] || { isfetching: false, isSuccess: false, isError: false };
