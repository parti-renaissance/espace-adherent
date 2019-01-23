import { UPDATE_STATIC } from '../constants/actionTypes';

const initialState = {
    committees: [],
    categories: [],
    needs: [],
    themes: [],
    reasons: [],
};

function staticReducer(state = initialState, action) {
    const { type, payload } = action;
    switch (type) {
    case UPDATE_STATIC:
        return { ...state, ...payload };
    default:
        return state;
    }
}

export default staticReducer;

export const getStatic = state => state;
