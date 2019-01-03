import { action } from '../helpers/actions';
import { SET_MY_CONTRIBUTIONS } from '../constants/actionTypes';

export const setMyContributions = (items = [], metadata = {}) =>
    action(SET_MY_CONTRIBUTIONS, { items, metadata });
