import { action } from '../helpers/actions';
import { SET_MY_IDEAS, REMOVE_MY_IDEA } from '../constants/actionTypes';

export const setMyIdeas = (items = [], metadata = {}) => action(SET_MY_IDEAS, { items, metadata });
export const removeMyIdea = id => action(REMOVE_MY_IDEA, { id });
