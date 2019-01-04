import { combineReducers } from 'redux';
import {
    SET_CURRENT_IDEA,
    UPDATE_CURRENT_IDEA,
    SET_GUIDELINES,
    TOGGLE_VOTE_CURRENT_IDEA,
} from '../constants/actionTypes';

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
    case TOGGLE_VOTE_CURRENT_IDEA: {
        const { typeVote } = payload;
        let voteCount = state.votes_count[typeVote];
        let total = state.votes_count.total;
        let myVotes = state.votes_count.my_votes;
        // my_votes exists
        if (myVotes && myVotes.length) {
            // remove vote
            if (myVotes.includes(typeVote)) {
                voteCount -= 1;
                total -= 1;
                myVotes = state.votes_count.my_votes.filter(
                    vote => vote !== typeVote
                );
            } else {
                // vote
                voteCount += 1;
                total += 1;
                myVotes = [...state.votes_count.my_votes, typeVote];
            }
        } else {
            // vote -> create new array my_vote
            voteCount += 1;
            total += 1;
            myVotes = [typeVote];
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

export default combineReducers({
    idea: ideaReducer,
    guidelines: guidelinesReducer,
});

export const getCurrentIdea = state => state.idea;
export const getGuidelines = state => state.guidelines;
