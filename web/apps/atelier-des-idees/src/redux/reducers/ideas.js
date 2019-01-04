import {
    SET_IDEAS,
    ADD_IDEAS,
    REMOVE_IDEA,
    TOGGLE_VOTE_IDEA,
} from '../constants/actionTypes';

export const initialState = { items: [], metadata: {} };

const ideasReducer = (state = initialState, action) => {
    const { type, payload } = action;
    switch (type) {
    case SET_IDEAS: {
        const { items, metadata } = payload;
        return { items, metadata };
    }
    case ADD_IDEAS: {
        const { items, metadata } = payload;
        return { items: [...state.items, ...items], metadata };
    }
    case REMOVE_IDEA: {
        return {
            ...state,
            items: state.items.filter(idea => idea.uuid !== payload.id),
        };
    }
    case TOGGLE_VOTE_IDEA: {
        const { id, typeVote } = payload;
        const updatedItems = state.items.filter((item) => {
            if (item.uuid === id) {
                let voteCount = item.votes_count[typeVote];
                let total = item.votes_count.total;
                let myVotes = item.votes_count.my_votes;
                // my_votes exists
                if (item.votes_count.my_votes && item.votes_count.my_votes.length) {
                    // remove vote
                    if (item.votes_count.my_votes.includes(typeVote)) {
                        voteCount -= 1;
                        total -= 1;
                        myVotes = item.votes_count.my_votes.filter(
                            vote => vote !== typeVote
                        );
                    } else {
                        // vote
                        voteCount += 1;
                        total += 1;
                        myVotes = [...item.votes_count.my_votes, typeVote];
                    }
                } else {
                    // vote -> create new array my_vote
                    voteCount += 1;
                    total += 1;
                    myVotes = [typeVote];
                }
                item.votes_count = {
                    ...item.votes_count,
                    [typeVote]: voteCount,
                    total,
                    my_votes: myVotes,
                };
            }
            return item;
        });
        return {
            ...state,
            items: updatedItems,
        };
    }
    default:
        return state;
    }
};

export default ideasReducer;

export const getIdeas = state => state.items;
export const getIdeasMetadata = state => state.metadata;
export const getIdeasWithStatus = (state, status) =>
    state.items.filter(idea => idea.status === status);
export const getFinalizedIdeas = state =>
    state.items.filter(idea => 'finalized' === idea.status);
