import { action } from '../helpers/actions';
import { SHOW_HEADER, HIDE_HEADER } from '../constants/actionTypes';

export const showHeader = () => action(SHOW_HEADER);
export const hideHeader = () => action(HIDE_HEADER);
