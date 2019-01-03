import { action } from '../helpers/actions';
import { SET_MY_IDEAS } from '../constants/actionTypes';

export const setMyIdeas = (items = [], metadata = {}) =>
    action(SET_MY_IDEAS, { items, metadata });
