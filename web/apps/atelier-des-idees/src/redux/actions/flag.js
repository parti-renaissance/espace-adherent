import { action } from '../helpers/actions';
import { ADD_FLAG } from '../constants/actionTypes';

export const addFlag = data => action(ADD_FLAG, { data });
