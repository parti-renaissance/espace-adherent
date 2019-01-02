import { SHOW_HEADER, HIDE_HEADER } from '../constants/actionTypes';

const initialState = { showHeader: true };

function uiReducer(state = initialState, action) {
    const { type } = action;
    switch (type) {
    case SHOW_HEADER:
        return { ...state, showHeader: true };
    case HIDE_HEADER:
        return { ...state, showHeader: false };
    default:
        return state;
    }
}

export default uiReducer;

export const getShowHeader = state => state.showHeader;
