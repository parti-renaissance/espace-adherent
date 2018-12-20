import { createRequestTypes } from '../helpers/actions';

// modal
export const SHOW_MODAL = 'SHOW_MODAL';
export const HIDE_MODAL = 'HIDE_MODAL';

// ideas
export const FETCH_IDEAS = createRequestTypes('FETCH_IDEAS');
export const SET_IDEAS = 'SET_IDEAS';
export const ADD_IDEAS = 'ADD_IDEAS';
