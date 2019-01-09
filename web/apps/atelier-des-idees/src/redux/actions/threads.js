import { action } from '../helpers/actions';
import { SET_THREADS, ADD_THREADS, REMOVE_THREAD, TOGGLE_APPROVE_THREAD } from '../constants/actionTypes';

export const setThreads = data => action(SET_THREADS, { data });
export const addThreads = data => action(ADD_THREADS, { data });
export const removeThread = id => action(REMOVE_THREAD, { id });
export const toggleApproveThread = id => action(TOGGLE_APPROVE_THREAD, { id });
