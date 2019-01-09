import { combineReducers } from 'redux';
import { SET_THREADS, ADD_THREADS, REMOVE_THREAD, TOGGLE_APPROVE_THREAD } from '../constants/actionTypes';

const initialState = { threads: [], comments: { items: [], metadata: {} } };

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

export default combineReducers({ threads: threadsReducer, comments: commentsReducer });

export const getAnswerThreads = (state, answerId) => state.threads.filter(thread => thread.answer.id === answerId);
export const getThread = (state, id) => state.threads.find(thread => thread.uuid === id);
