import { SET_IDEAS, ADD_IDEAS } from '../constants/actionTypes';

export const initialState = [];

const ideasReducer = (state = initialState, action) => {
    const { type, payload } = action;
    switch (type) {
    case SET_IDEAS:
        return payload.ideas;
    case ADD_IDEAS: {
        return [...state, ...payload.ideas];
    }
    default:
        return state;
    }
};

export default ideasReducer;

export const getIdeas = state => state;
export const getIdeasWithStatus = (state, status) => state.filter(idea => idea.status === status);
export const getFinalizedIdeas = state => state.filter(idea => 'finalized' === idea.status);
