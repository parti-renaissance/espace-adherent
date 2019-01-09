import { FETCH_IDEAS, FETCH_IDEA, SAVE_IDEA, PUBLISH_IDEA, FETCH_MY_IDEAS, VOTE_IDEA } from '../constants/actionTypes';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { addIdeas, setIdeas, removeIdea, toggleVoteIdea } from '../actions/ideas';
import { setCurrentIdea } from '../actions/currentIdea';
import { selectIdeasMetadata } from '../selectors/ideas';
import { setMyIdeas, removeMyIdea } from '../actions/myIdeas';
import { setMyContributions } from '../actions/myContributions';
import { selectAuthUser, selectIsAuthenticated } from '../selectors/auth';
import { hideModal } from '../actions/modal';
import { setThreads } from '../actions/threads';

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
        const { current_page, last_page } = metadata;
        if (current_page !== last_page) {
            const pagingParams = { page: current_page + 1 };
            return dispatch(fetchIdeas(status, { ...params, ...pagingParams }));
        }
    };
}

/**
 *
 * @param {string} id idea
 */
export function fetchIdea(id) {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_IDEA, id));
        return axios
            .get(`/api/ideas/${id}`)
            .then(res => res.data)
            .then((data) => {
                dispatch(
                    setCurrentIdea({
                        ...data,
                        uuid: id,
                    })
                );
                // (kinda) normalize idea threads
                const threads = data.answers.reduce((acc, answer) => {
                    if (answer.threads) {
                        const answerThreads = answer.threads.items.map(item => ({
                            ...item,
                            answer: { id: answer.id },
                        }));
                        return [...acc, ...answerThreads];
                    }
                    return acc;
                }, []);
                dispatch(setThreads(threads));

                dispatch(createRequestSuccess(FETCH_IDEA, id));
            })
            .catch(error => dispatch(createRequestFailure(FETCH_IDEA, id)));
    };
}

/**
 * Fetch ideas of current auth user
 * @param {object} params Query params
 */
export function fetchUserIdeas(params = {}) {
    return (dispatch, getState, axios) => {
        const isAuthenticated = selectIsAuthenticated(getState());
        if (isAuthenticated) {
            const user = selectAuthUser(getState());
            dispatch(createRequest(FETCH_MY_IDEAS));
            return axios
                .get(`/api/ideas?author.uuid=${user.uuid}`, { params: { ...params } })
                .then(res => res.data)
                .then(({ items, metadata }) => {
                    dispatch(setMyIdeas(items, metadata));
                    dispatch(createRequestSuccess(FETCH_MY_IDEAS));
                })
                .catch((error) => {
                    dispatch(createRequestFailure(FETCH_MY_IDEAS));
                });
        }
    };
}

/**
 * Fetch contributions of current auth user
 * @param {object} params Query params
 */
export function fetchUserContributions(params = {}) {
    return (dispatch, getState, axios) => {
        const isAuthenticated = selectIsAuthenticated(getState());
        if (isAuthenticated) {
            dispatch(createRequest(FETCH_MY_IDEAS));
            return axios
                .get('/api/ideas/my-contributions', { params: { ...params } })
                .then(res => res.data)
                .then(({ items, metadata }) => {
                    dispatch(setMyContributions(items, metadata));
                    dispatch(createRequestSuccess(FETCH_MY_IDEAS));
                })
                .catch((error) => {
                    dispatch(createRequestFailure(FETCH_MY_IDEAS));
                });
        }
    };
}

export function voteIdea(id, vote) {
    return (dispatch, getState, axios) => {
        const isAuthenticated = selectIsAuthenticated(getState());
        if (isAuthenticated) {
            dispatch(toggleVoteIdea(id, vote));
            dispatch(createRequest(VOTE_IDEA, id));
            const requestBody = {
                method: 'POST',
                url: '/api/votes',
                data: { idea: id, type: vote },
            };
            return axios(requestBody)
                .then(res => res.data)
                .then(() => {
                    dispatch(createRequestSuccess(VOTE_IDEA, id));
                })
                .catch(() => {
                    dispatch(toggleVoteIdea(id, vote));
                    dispatch(createRequestFailure(VOTE_IDEA, id));
                });
        }
        window.location = '/connexion';
    };
}

export function saveIdea(id, ideaData) {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(SAVE_IDEA, id));
        const requestBody = id
            ? { method: 'PUT', url: `/api/ideas/${id}`, data: ideaData }
            : { method: 'POST', url: '/api/ideas', data: ideaData };
        return axios(requestBody)
            .then(res => res.data)
            .then(() => dispatch(createRequestSuccess(SAVE_IDEA, id)))
            .catch((error) => {
                dispatch(createRequestFailure(SAVE_IDEA, id));
                throw error;
            });
    };
}

export function deleteIdea(id) {
    return (dispatch, getState, axios) =>
        axios.delete(`/api/ideas/${id}`).then(() => {
            // remove idea entity
            dispatch(removeIdea(id));
            dispatch(removeMyIdea(id));
            // hide modal
            dispatch(hideModal());
        });
}

export function publishIdea(id) {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(PUBLISH_IDEA, id));
        return axios
            .put(`/api/ideas/${id}/publish`)
            .then(res => res.data)
            .then(() => dispatch(createRequestSuccess(PUBLISH_IDEA, id)))
            .catch(() => dispatch(createRequestFailure(PUBLISH_IDEA, id)));
    };
}

export function saveAndPublishIdea(uuid, data) {
    return dispatch =>
        dispatch(saveIdea(uuid, data)).then((resData) => {
            dispatch(publishIdea(uuid || resData.uuid));
        });
}
