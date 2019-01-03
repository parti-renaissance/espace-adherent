import { SET_IDEAS, ADD_IDEAS, REMOVE_IDEA } from '../constants/actionTypes';

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
        return { ...state, items: state.items.filter(idea => idea.uuid !== payload.id) };
    }
    default:
        return state;
    }
};

export default ideasReducer;

export const getIdeas = state => state.items;
export const getIdeasMetadata = state => state.metadata;
export const getIdeasWithStatus = (state, status) => state.items.filter(idea => idea.status === status);
export const getFinalizedIdeas = state => state.items.filter(idea => 'finalized' === idea.status);
