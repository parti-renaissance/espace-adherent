import { FETCH_IDEAS } from '../constants/actionTypes';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { addIdeas } from '../actions/ideas';

export function fetchIdeas(status, params = {}) {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_IDEAS, status));
        return axios
            .get('/api/ideas', { params: { status, ...params } })
            .then(res => res.data)
            .then((ideas) => {
                dispatch(addIdeas(ideas));
                dispatch(createRequestSuccess(FETCH_IDEAS, status));
            })
            .catch(error => dispatch(createRequestFailure(FETCH_IDEAS, status)));
    };
}
