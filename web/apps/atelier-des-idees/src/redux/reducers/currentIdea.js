import { SET_CURRENT_IDEA, UPDATE_CURRENT_IDEA, SET_GUIDELINES } from '../constants/actionTypes';

const initialState = { idea: {}, guidelines: [] };

function currentIdeaReducer(state = initialState, action) {
    const { type, payload } = action;
    switch (type) {
    case SET_CURRENT_IDEA: {
        return { ...state, idea: payload.data };
    }
    case UPDATE_CURRENT_IDEA: {
        return { ...state, idea: { ...state.idea, ...payload.data } };
    }
    case SET_GUIDELINES: {
        return { ...state, guidelines: payload.data };
    }
    default:
        return state;
    }
}

export default currentIdeaReducer;

export const getCurrentIdea = state => state.idea;
export const getGuidelines = state => state.guidelines;
