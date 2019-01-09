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

const newAnswersMock = [
    {
        id: 586,
        content: 'Mauris gravida semper tincidunt.',
        question: {
            id: 521,
        },
        threads: {
            total_items: 3,
            items: [
                {
                    comments: {
                        total_items: 3,
                        items: [
                            {
                                uuid: '001a53d0-1134-429c-8dc1-c57643b3f069',
                                content: 'Commentaire refus\u00e9',
                                author: {
                                    uuid: '93de5d98-383a-4863-9f47-eb7a348873a8',
                                    first_name: 'Laura',
                                    last_name: 'Deloche',
                                },
                                created_at: new Date().toISOString(),
                            },
                            {
                                uuid: '3fa38c45-1122-4c48-9ada-b366b3408fec',
                                content: 'Commentaire signal\u00e9',
                                author: {
                                    uuid: '93de5d98-383a-4863-9f47-eb7a348873a8',
                                    first_name: 'Laura',
                                    last_name: 'Deloche',
                                },
                                created_at: new Date().toISOString(),
                            },
                            {
                                uuid: '02bf299f-678a-4829-a6a1-241995339d8d',
                                content: 'Commentaire de Laura',
                                author: {
                                    uuid: '93de5d98-383a-4863-9f47-eb7a348873a8',
                                    first_name: 'Laura',
                                    last_name: 'Deloche',
                                },
                                created_at: new Date().toISOString(),
                            },
                        ],
                    },
                    uuid: 'a508a7c5-8b07-41f4-8515-064f674a65e8',
                    content: 'J\'ouvre une discussion sur la comparaison.',
                    author: {
                        uuid: 'b4219d47-3138-5efd-9762-2ef9f9495084',
                        first_name: 'Gisele',
                        last_name: 'Berthoux',
                    },
                    created_at: new Date().toISOString(),
                },
                {
                    comments: {
                        total_items: 0,
                        items: [],
                    },
                    uuid: '78d7daa1-657c-4e7e-87bc-24eb4ea26ea2',
                    content: 'Une discussion refus\u00e9e.',
                    author: {
                        uuid: 'b4219d47-3138-5efd-9762-2ef9f9495084',
                        first_name: 'Gisele',
                        last_name: 'Berthoux',
                    },
                    created_at: new Date().toISOString(),
                },
                {
                    comments: {
                        total_items: 0,
                        items: [],
                    },
                    uuid: 'b191f13a-5a05-49ed-8ec3-c335aa68f439',
                    content: 'Une discussion signal\u00e9e.',
                    author: {
                        uuid: 'b4219d47-3138-5efd-9762-2ef9f9495084',
                        first_name: 'Gisele',
                        last_name: 'Berthoux',
                    },
                    created_at: new Date().toISOString(),
                },
            ],
        },
    },
    {
        id: 588,
        content:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce aliquet, mi condimentum venenatis vestibulum, arcu neque feugiat massa, at pharetra velit sapien et elit. Sed vitae hendrerit nulla. Vivamus consectetur magna at tincidunt maximus. Aenean dictum metus vel tellus posuere venenatis.',
        question: {
            id: 523,
        },
        threads: {
            total_items: 1,
            items: [
                {
                    comments: {
                        total_items: 4,
                        items: [
                            {
                                uuid: 'ecbe9136-3dc0-477d-b817-a25878dd639a',
                                content: 'Deuxi\u00e8me commentaire d\'un r\u00e9f\u00e9rent',
                                author: {
                                    uuid: '29461c49-2646-4d89-9c82-50b3f9b586f4',
                                    first_name: 'Referent',
                                    last_name: 'Referent',
                                },
                                created_at: new Date().toISOString(),
                            },
                            {
                                uuid: 'f716d3ba-004f-4958-af26-a7b010a6d458',
                                content: 'Commentaire d\'un r\u00e9f\u00e9rent',
                                author: {
                                    uuid: '29461c49-2646-4d89-9c82-50b3f9b586f4',
                                    first_name: 'Referent',
                                    last_name: 'Referent',
                                },
                                created_at: new Date().toISOString(),
                            },
                            {
                                uuid: '60123090-6cdc-4de6-9cb3-07e2ec411f2f',
                                content: 'Lorem Ipsum Commentaris',
                                author: {
                                    uuid: 'a9fc8d48-6f57-4d89-ae73-50b3f9b586f4',
                                    first_name: 'Francis',
                                    last_name: 'Brioul',
                                },
                                created_at: new Date().toISOString(),
                            },
                        ],
                    },
                    uuid: 'dfd6a2f2-5579-421f-96ac-98993d0edea1',
                    content: 'J\'ouvre une discussion sur le probl\u00e8me.',
                    author: {
                        uuid: 'e6977a4d-2646-5f6c-9c82-88e58dca8458',
                        first_name: 'Carl',
                        last_name: 'Mirabeau',
                    },
                    created_at: new Date().toISOString(),
                },
            ],
        },
    },
];

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
                // TODO: remove use of mock
                // data.answers = newAnswersMock;
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
