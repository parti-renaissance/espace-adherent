import { SET_CURRENT_IDEA, UPDATE_CURRENT_IDEA } from '../constants/actionTypes';

const initialState = {};

function currentIdeaReducer(state = initialState, action) {
    const { type, payload } = action;
    switch (type) {
    case SET_CURRENT_IDEA: {
        return { ...payload.data };
    }
    case UPDATE_CURRENT_IDEA: {
        return { ...state, ...payload.data };
    }
    default:
        return state;
    }
}

export default currentIdeaReducer;

export const getCurrentIdea = state => state;
