import { combineReducers } from 'redux';
import {
    SET_THREADS,
    ADD_THREADS,
    REMOVE_THREAD,
    TOGGLE_APPROVE_THREAD,
    SET_ANSWER_THREADS_PAGING,
} from '../constants/actionTypes';

const initialState = { threads: [], comments: { items: [], metadata: {} }, paging: {} };

function threadsReducer(state = initialState.threads, action) {
    const { type, payload } = action;
    switch (type) {
    case SET_THREADS:
        return payload.data;
    case ADD_THREADS:
        return [...state, ...payload.data];
    case REMOVE_THREAD: {
        const { id } = payload;
        return state.filter(thread => thread.uuid !== id);
    }
    case TOGGLE_APPROVE_THREAD: {
        const { id } = payload;
        const threads = state.map((thread) => {
            if (thread.uuid === id) {
                return { ...thread, approved: !thread.approved };
            }
            return thread;
        });
        return threads;
    }
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

function pagingReducer(state = initialState.paging, action) {
    const { type, payload } = action;
    switch (type) {
    case SET_ANSWER_THREADS_PAGING:
        return { ...state, [payload.answerId]: payload.data };
    default:
        return state;
    }
}

export default combineReducers({ threads: threadsReducer, comments: commentsReducer, paging: pagingReducer });

export const getAnswerThreads = (state, answerId) =>
    state.threads.filter(thread => thread.answer && thread.answer.id === answerId);
export const getThread = (state, id) => state.threads.find(thread => thread.uuid === id);
export const getAnswerThreadsPagingData = (state, answerId) => state.paging[answerId];
