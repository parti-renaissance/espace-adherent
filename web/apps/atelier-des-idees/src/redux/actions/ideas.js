import { action } from '../helpers/actions';
import { SET_IDEAS, ADD_IDEAS } from '../constants/actionTypes';

export const setIdeas = (items = [], metadata = {}) => action(SET_IDEAS, { items, metadata });
export const addIdeas = (items = [], metadata = {}) => action(ADD_IDEAS, { items, metadata });
