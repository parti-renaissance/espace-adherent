import { FETCH_IDEAS } from '../constants/actionTypes';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { addIdeas, setIdeas } from '../actions/ideas';
import { selectIdeasMetadata } from '../selectors/ideas';

/**
 * Fetch ideas based on status and parameters
 * @param {string} status Ideas status to fetch
 * @param {object} params Query params
 * @param {boolean} setMode If true, set ideas by erasing previous ones. Default: false
 */
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

/**
 * Fetch next ideas based on current ideas metadata
 * @param {string} status Ideas status to fetch
 * @param {object} params Query params
 */
export function fetchNextIdeas(status, params = {}) {
    return (dispatch, getState) => {
        const metadata = selectIdeasMetadata(getState());
        const pagingParams = {}; // TODO: compute params based on metadata
        return dispatch(fetchIdeas(status, { ...params, ...pagingParams }));
    };
}

/**
 * Delete an idea
 * @param {string} id idea to delete
 */
export function deleteIdea(id = '') {
    // TODO: add logic (delete idea, draft?, redirect? )
    return (dispatch, getState, axios) => {
        if (id) {
            return axios.delete('/api/idea');
        }
        return (window.location = '/atelier-des-idees');
    };
}
