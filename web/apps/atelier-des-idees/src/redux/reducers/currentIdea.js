import { combineReducers } from 'redux';
import {
    SET_CURRENT_IDEA,
    UPDATE_CURRENT_IDEA,
    SET_GUIDELINES,
    TOGGLE_VOTE_CURRENT_IDEA,
    UPDATE_CURRENT_IDEA_ANSWER,
    SET_AUTOCOMPLETE_RESULT,
    EXTEND_PERIOD,
} from '../constants/actionTypes';
import { toggleVote } from './ideas';

const initialState = { idea: {}, guidelines: [], threads: [], autoComplete: [] };

// TODO: refactor reducers to store current idea data in ideas reducer (normalizr style)

function ideaReducer(state = initialState.idea, action) {
    const { type, payload } = action;
    switch (type) {
    case SET_CURRENT_IDEA: {
        return { ...payload.data };
    }
    case SET_AUTOCOMPLETE_RESULT:
        return { ...state, autoComplete: payload.data };
    case UPDATE_CURRENT_IDEA: {
        return { ...state, ...payload.data };
    }
    case UPDATE_CURRENT_IDEA_ANSWER: {
        const { answerId, data } = payload;
        const { answers } = state;
        const updatedAnswers = answers.map((answer) => {
            if (answer.id === answerId) {
                return { ...answer, ...data };
            }
            return answer;
        });
        return { ...state, answers: updatedAnswers };
    }
    case EXTEND_PERIOD:
        return { ...state, extendable: payload.data };
    case TOGGLE_VOTE_CURRENT_IDEA: {
        const { voteType, voteId } = payload;
        return toggleVote(state, voteType, voteId);
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
export const getCurrentIdeaAnswer = (state, answerId) =>
    state.idea.answers && state.idea.answers.find(answer => answer.id === answerId);
export const getGuidelines = state => state.guidelines;

export const getAutoComplete = state => state.autoComplete;
