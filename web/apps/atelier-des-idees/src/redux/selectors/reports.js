import { getReports } from '../reducers/reports';

export const selectReports = state => getReports(state.reports);
