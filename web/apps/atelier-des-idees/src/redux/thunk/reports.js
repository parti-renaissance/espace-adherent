import { FETCH_REPORTS } from '../constants/actionTypes';
import { addReports } from '../actions/reports';
import {
    createRequest,
    createRequestSuccess,
    createRequestFailure,
} from '../actions/loading';

const reportsMock = [
    {
        file: '/',
        fileName: 'document-5.pdf',
        size: '1.2 Mb',
    },
    {
        file: '/',
        fileName: 'document-5.pdf',
        size: '1.2 Mb',
    },
    {
        file: '/',
        fileName: 'document-5.pdf',
        size: '1.2 Mb',
    },
    {
        file: '/',
        fileName: 'document-5.pdf',
        size: '1.2 Mb',
    },
    {
        file: '/',
        fileName: 'document-5.pdf',
        size: '1.2 Mb',
    },
];

export function fetchReports() {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_REPORTS));
        return (
            axios
        // TODO: Replace by real api
                .get('/api/reports')
                .then(res => res.data)
                .then((data) => {
                    dispatch(addReports(data));
                    dispatch(createRequestSuccess(FETCH_REPORTS));
                })
                .catch((error) => {
                    dispatch(createRequestFailure(FETCH_REPORTS));
                })
        // TODO: remove finally when endpoint is up
                .finally(() => dispatch(addReports(reportsMock)))
        );
    };
}
