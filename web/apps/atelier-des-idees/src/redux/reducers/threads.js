import { combineReducers } from 'redux';

const initialState = { threads: { items: [], metadata: {} }, comments: { items: [], metadata: {} } };

function threadsReducer(state = initialState.threads, action) {
    const { type, payload } = action;
    switch (type) {
    default:
        return state;
    }
}

function commentsReducer(state = initialState.comments, action) {
    const { type, payload } = action;
    switch (type) {
    default:
        return state;
    }
}

export default combineReducers({ threads: threadsReducer, comments: commentsReducer });
