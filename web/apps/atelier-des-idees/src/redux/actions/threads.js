import { action } from '../helpers/actions';
import { SET_THREADS, ADD_THREAD } from '../constants/actionTypes';

export const setThreads = data => action(SET_THREADS, { data });
export const addThread = data => action(ADD_THREAD, { data });
