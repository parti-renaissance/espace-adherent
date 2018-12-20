import { action } from '../helpers/actions';
import { SET_IDEAS, ADD_IDEAS } from '../constants/actionTypes';

export const setIdeas = (ideas = []) => action(SET_IDEAS, { ideas });
export const addIdeas = (ideas = []) => action(ADD_IDEAS, { ideas });
