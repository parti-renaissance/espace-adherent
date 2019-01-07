import { combineReducers } from 'redux';
import { SET_THREADS, ADD_THREAD } from '../constants/actionTypes';

const initialState = { threads: { items: [], metadata: {} }, comments: { items: [], metadata: {} } };

function threadsReducer(state = initialState.threads, action) {
    const { type, payload } = action;
    switch (type) {
    case SET_THREADS:
        return payload.data;
    case ADD_THREAD:
        return { ...state, items: [...state.items, payload.data] };
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

export const getAnswerThreads = (state, answerId) =>
    state.threads.items.find(thread => thread.answer.uuid === answerId);
