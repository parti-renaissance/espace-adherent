import { action } from '../helpers/actions';
import { SET_REPORTS } from '../constants/actionTypes';

export const setReports = (reports = []) => action(SET_REPORTS, { reports });
