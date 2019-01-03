import { action } from '../helpers/actions';
import { SET_FLAG_REASONS, POST_FLAG } from '../constants/actionTypes';

export const setFlagReasons = data => action(SET_FLAG_REASONS, { data });
export const postFlag = data => action(POST_FLAG, { data });
