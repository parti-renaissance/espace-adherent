import { combineReducers } from 'redux';
import {
    SET_CURRENT_IDEA,
    UPDATE_CURRENT_IDEA,
    SET_GUIDELINES,
    TOGGLE_VOTE_CURRENT_IDEA,
    UPDATE_CURRENT_IDEA_ANSWER,
} from '../constants/actionTypes';

const initialState = { idea: {}, guidelines: [], threads: [] };

function toggleVote(state, typeVote) {
    let voteCount = state.votes_count[typeVote];
    let total = state.votes_count.total;
    let myVotes = state.votes_count.my_votes;
    if (!myVotes) {
        // my_votes does not exist
        myVotes = [];
    }
    // remove vote
    if (myVotes.includes(typeVote)) {
        voteCount -= 1;
        total -= 1;
        myVotes = myVotes.filter(vote => vote !== typeVote);
    } else {
        // vote
        voteCount += 1;
        total += 1;
        myVotes = [...myVotes, typeVote];
    }
    return {
        ...state,
        votes_count: {
            ...state.votes_count,
            [typeVote]: voteCount,
            total,
            my_votes: myVotes,
        },
    };
}

function ideaReducer(state = initialState.idea, action) {
    const { type, payload } = action;
    switch (type) {
    case SET_CURRENT_IDEA: {
        return { ...payload.data };
    }
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
    case TOGGLE_VOTE_CURRENT_IDEA: {
        const { typeVote } = payload;
        return toggleVote(state, typeVote);
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
