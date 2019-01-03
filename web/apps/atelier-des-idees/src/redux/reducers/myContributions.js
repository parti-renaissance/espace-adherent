import { SET_MY_CONTRIBUTIONS } from '../constants/actionTypes';

export const initialState = { items: [], metadata: {} };

const ideasReducer = (state = initialState, action) => {
    const { type, payload } = action;
    switch (type) {
    case SET_MY_CONTRIBUTIONS: {
        const { items, metadata } = payload;
        return { items, metadata };
    }
    default:
        return state;
    }
};

export default ideasReducer;

export const getMyContributions = state => state.items;
export const getMyContributionsMetadata = state => state.metadata;
