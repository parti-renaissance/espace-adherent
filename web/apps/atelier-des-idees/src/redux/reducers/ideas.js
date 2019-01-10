import { SET_IDEAS, ADD_IDEAS, REMOVE_IDEA, TOGGLE_VOTE_IDEA } from '../constants/actionTypes';

export const initialState = { items: [], metadata: {} };

function toggleVote(idea, voteType, voteId) {
    let voteCount = parseInt(idea.votes_count[voteType], 10);
    let total = idea.votes_count.total;
    let myVotes = idea.votes_count.my_votes;
    if (!myVotes) {
        // my_votes does not exist
        myVotes = {};
    }
    if (Object.keys(myVotes).includes(voteType)) {
        // vote exists, remove it
        voteCount -= 1;
        total -= 1;
        myVotes = Object.entries(myVotes)
            .filter(([type]) => type !== voteType)
            .reduce((acc, [type, id]) => {
                acc[type] = id;
                return acc;
            }, {});
    } else {
        // vote
        voteCount += 1;
        total += 1;
        myVotes = { ...myVotes, [voteType]: voteId };
    }
    return {
        ...idea,
        votes_count: {
            ...idea.votes_count,
            [voteType]: voteCount,
            total,
            my_votes: myVotes,
        },
    };
}

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
        const { id, voteType } = payload;
        const updatedItems = state.items.map((item) => {
            if (item.uuid === id) {
                return toggleVote(item, voteType);
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
export const getIdea = (state, uuid) => state.items.find(idea => idea.uuid === uuid);
export const getIdeasMetadata = state => state.metadata;
export const getIdeasWithStatus = (state, status) => state.items.filter(idea => idea.status === status);
export const getFinalizedIdeas = state => state.items.filter(idea => 'finalized' === idea.status);
