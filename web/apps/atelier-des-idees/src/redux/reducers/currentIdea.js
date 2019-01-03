import { combineReducers } from 'redux';
import { SET_CURRENT_IDEA, UPDATE_CURRENT_IDEA, SET_GUIDELINES } from '../constants/actionTypes';

const initialState = { idea: {}, guidelines: [] };

function ideaReducer(state = initialState.idea, action) {
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

function guidelinesReducer(state = initialState.guidelines, action) {
    const { type, payload } = action;
    switch (type) {
    case SET_GUIDELINES: {
        return [...payload.data];
    }
    default:
        return state;
    }
}

export default combineReducers({ idea: ideaReducer, guidelines: guidelinesReducer });

export const getCurrentIdea = state => state.idea;
export const getGuidelines = state => state.guidelines;
