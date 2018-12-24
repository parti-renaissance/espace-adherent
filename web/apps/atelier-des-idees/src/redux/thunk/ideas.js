import { FETCH_IDEAS } from '../constants/actionTypes';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { addIdeas, setIdeas } from '../actions/ideas';

export function fetchIdeas(status, params = {}, setMode = false) {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_IDEAS, status));
        return axios
            .get('/api/ideas', { params: { status, ...params } })
            .then(res => res.data)
            .then(({ items, metadata }) => {
                if (setMode) {
                    dispatch(setIdeas(items, metadata));
                } else {
                    dispatch(addIdeas(items, metadata));
                }
                dispatch(createRequestSuccess(FETCH_IDEAS, status));
            })
            .catch((error) => {
                dispatch(createRequestFailure(FETCH_IDEAS, status));
            });
    };
}

export function fetchNextIdeas(status, params = {}) {
    return (dispatch, getState, axios) => {};
}
