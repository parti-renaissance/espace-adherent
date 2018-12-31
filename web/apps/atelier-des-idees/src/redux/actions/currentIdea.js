import { action } from '../helpers/actions';
import { SET_CURRENT_IDEA, UPDATE_CURRENT_IDEA } from '../constants/actionTypes';

export const setCurrentIdea = data => action(SET_CURRENT_IDEA, { data });
export const updateCurrentIdea = data => action(UPDATE_CURRENT_IDEA, { data });
