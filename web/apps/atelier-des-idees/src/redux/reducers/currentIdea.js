import { SET_CURRENT_IDEA } from '../constants/actionTypes';

const initialState = null;

function currentIdeaReducer(state = initialState, action) {
    const { type, payload } = action;
    switch (type) {
    case SET_CURRENT_IDEA: {
        return { ...payload.data };
    }
    default:
        return state;
    }
}

export default currentIdeaReducer;
