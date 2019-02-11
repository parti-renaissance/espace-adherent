import { action } from '../helpers/actions';
import { SET_MY_IDEAS, REMOVE_MY_IDEA } from '../constants/actionTypes';

export const setMyIdeas = (items = [], metadata = {}, namespace) =>
    action(SET_MY_IDEAS, { items, metadata, namespace });
export const removeMyIdea = id => action(REMOVE_MY_IDEA, { id });
