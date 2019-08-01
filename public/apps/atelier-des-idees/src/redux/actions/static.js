import { action } from '../helpers/actions';
import { UPDATE_STATIC } from '../constants/actionTypes';

export const updateStatic = data => action(UPDATE_STATIC, { ...data });
