import { action } from '../helpers/actions';
import { SET_THREADS, ADD_THREAD, REMOVE_THREAD } from '../constants/actionTypes';

export const setThreads = data => action(SET_THREADS, { data });
export const addThread = data => action(ADD_THREAD, { data });
export const removeThread = id => action(REMOVE_THREAD, { id });
