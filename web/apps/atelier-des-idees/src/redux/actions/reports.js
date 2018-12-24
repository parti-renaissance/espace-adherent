import { action } from '../helpers/actions';
import { ADD_REPORTS } from '../constants/actionTypes';

export const addReports = (reports = []) => action(ADD_REPORTS, { reports });
