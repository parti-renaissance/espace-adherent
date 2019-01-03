import { FETCH_REPORTS } from '../constants/actionTypes';
import { setReports } from '../actions/reports';
import {
    createRequest,
    createRequestSuccess,
    createRequestFailure,
} from '../actions/loading';

export function fetchReports() {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_REPORTS));
        return axios
            .get('/api/consultation_reports')
            .then(res => res.data)
            .then(({ items, metadata }) => {
                dispatch(setReports(items));
                dispatch(createRequestSuccess(FETCH_REPORTS));
            })
            .catch((error) => {
                dispatch(createRequestFailure(FETCH_REPORTS));
            });
    };
}
