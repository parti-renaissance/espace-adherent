import { action } from '../helpers/actions';
import {
    SET_THREADS,
    ADD_THREADS,
    REMOVE_THREAD,
    UPDATE_THREAD,
    TOGGLE_APPROVE_THREAD,
    SET_THREAD_COMMENTS,
    ADD_THREAD_COMMENTS,
    REMOVE_THREAD_COMMENT,
    TOGGLE_APPROVE_THREAD_COMMENT,
    SET_THREAD_PAGING_DATA,
    RESET_THREAD_PAGING_DATA,
} from '../constants/actionTypes';

export const setThreads = data => action(SET_THREADS, { data });
export const addThreads = data => action(ADD_THREADS, { data });
export const updateThread = (id, data) => action(UPDATE_THREAD, { id, data });
export const removeThread = id => action(REMOVE_THREAD, { id });
export const toggleApproveThread = id => action(TOGGLE_APPROVE_THREAD, { id });
export const setThreadComments = data => action(SET_THREAD_COMMENTS, { data });
export const addThreadComments = data => action(ADD_THREAD_COMMENTS, { data });
export const removeThreadComment = id => action(REMOVE_THREAD_COMMENT, { id });
export const toggleApproveThreadComment = id => action(TOGGLE_APPROVE_THREAD_COMMENT, { id });
// paging
export const setThreadPagingData = (id, data) => action(SET_THREAD_PAGING_DATA, { id, data });
export const resetThreadPagingData = () => action(RESET_THREAD_PAGING_DATA);
