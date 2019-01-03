import { SET_MY_IDEAS } from '../constants/actionTypes';

export const initialState = { items: [], metadata: {} };

const ideasReducer = (state = initialState, action) => {
    const { type, payload } = action;
    switch (type) {
    case SET_MY_IDEAS: {
        const { items, metadata } = payload;
        return { items, metadata };
    }
    default:
        return state;
    }
};

export default ideasReducer;

export const getMyIdeas = state => state.items;
export const getMyIdeasMetadata = state => state.metadata;
