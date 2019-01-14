import Axios from 'axios';
import { FETCH_IDEAS, FETCH_IDEA, SAVE_IDEA, PUBLISH_IDEA, FETCH_MY_IDEAS, VOTE_IDEA } from '../constants/actionTypes';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { addIdeas, setIdeas, removeIdea, toggleVoteIdea } from '../actions/ideas';
import { setCurrentIdea } from '../actions/currentIdea';
import { selectIdeasMetadata, selectIdea } from '../selectors/ideas';
import { setMyIdeas, removeMyIdea } from '../actions/myIdeas';
import { setMyContributions } from '../actions/myContributions';
import { selectAuthUser, selectIsAuthenticated } from '../selectors/auth';
import { hideModal } from '../actions/modal';
import { setThreads, setThreadComments } from '../actions/threads';

// axios cancellation
const CancelToken = Axios.CancelToken;
let cancel;
/**
 * Fetch ideas based on status and parameters
 * @param {string} status Ideas status to fetch
 * @param {object} params Query params
 * @param {boolean} setMode If true, set ideas by erasing previous ones. Default: false
 */
export function fetchIdeas(status, params = {}, options = { setMode: false, cancel: false }) {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_IDEAS, status));
        if (options.setMode) {
            dispatch(setIdeas([], {}));
        }
        if (options.cancel) {
            cancel();
        }
        return axios
            .get('/api/ideas-workshop/ideas', {
                params: { status, ...params },
                cancelToken: new CancelToken((c) => {
                    cancel = c;
                }),
            })
            .then(res => res.data)
            .then(({ items, metadata }) => {
                if (options.setMode) {
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
            .get(`/api/ideas-workshop/ideas/${id}`)
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
                // set thread comments
                const comments = threads.reduce((acc, thread) => {
                    // add parent thread id to each comment
                    const threadComments = thread.comments.items.map(threadComment => ({
                        ...threadComment,
                        thread: { uuid: thread.uuid },
                    }));
                    return [...acc, ...threadComments];
                }, []);
                dispatch(setThreadComments(comments));
                //  request success state
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
                .get(`/api/ideas-workshop/ideas?author.uuid=${user.uuid}`, { params: { ...params } })
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
                .get('/api/ideas-workshop/ideas/my-contributions', { params: { ...params } })
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

export function postVote(uuid, voteType) {
    return (dispatch, getState, axios) =>
        axios
            .post('/api/ideas-workshop/votes', { idea: uuid, type: voteType })
            .then(res => res.data)
            .then((voteData) => {
                dispatch(toggleVoteIdea(uuid, { voteType, voteId: voteData.id }));
                return voteData;
            })
            .catch((error) => {
                throw error;
            });
}

export function cancelVote(uuid, voteType, voteId) {
    return (dispatch, getState, axios) => {
        dispatch(toggleVoteIdea(uuid, { voteType }));
        return axios.delete(`/api/ideas-workshop/votes/${voteId}`).catch((error) => {
            dispatch(toggleVoteIdea(uuid, { voteType, voteId }));
            throw error;
        });
    };
}

/**
 * Toggle a vote on an idea
 * @param {string} id idea's uuid
 * @param {string} voteType type of toggled vote
 * @param {object} baseIdea optional, can be used to override ideas reducer item
 */
export function voteIdea(uuid, voteType, baseIdea) {
    return (dispatch, getState, axios) => {
        const isAuthenticated = selectIsAuthenticated(getState());
        if (isAuthenticated) {
            const idea = baseIdea || selectIdea(getState(), uuid);
            const { votes_count } = idea;
            if (votes_count.my_votes && Object.keys(votes_count.my_votes).includes(voteType)) {
                // user has already voted that, cancel vote
                return dispatch(cancelVote(uuid, voteType, votes_count.my_votes[voteType]));
            }
            // user has not already voted that, post vote
            return dispatch(postVote(uuid, voteType));
        }
        window.location = '/connexion';
    };
}

export function saveIdea(id, ideaData) {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(SAVE_IDEA, id));
        const requestBody = id
            ? { method: 'PUT', url: `/api/ideas-workshop/ideas/${id}`, data: ideaData }
            : { method: 'POST', url: '/api/ideas-workshop/ideas', data: ideaData };
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
        axios.delete(`/api/ideas-workshop/ideas/${id}`).then(() => {
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
            .put(`/api/ideas-workshop/ideas/${id}/publish`)
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
