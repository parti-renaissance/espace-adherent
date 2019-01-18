import { ideaStatus } from '../../constants/api';
import history from '../../history';
import { SAVE_CURRENT_IDEA, FETCH_GUIDELINES } from '../constants/actionTypes';
import { saveIdea, publishIdea, voteIdea } from '../thunk/ideas';
import { postComment, fetchThreads, deleteComment } from '../thunk/threads';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { selectIsAuthenticated } from '../selectors/auth';
import { selectCurrentIdea, selectCurrentIdeaAnswer } from '../selectors/currentIdea';
import { selectAnswerThreads, selectThread, selectAnswerThreadsPagingData } from '../selectors/threads';
import {
    setCurrentIdea,
    updateCurrentIdea,
    setGuidelines,
    toggleVoteCurrentIdea,
    updateCurrentIdeaAnswer,
} from '../actions/currentIdea';
import { addThreads, addThreadComments, updateThread, setThreadPagingData } from '../actions/threads';
import { hideModal } from '../actions/modal';

/**
 * Delete an idea
 * @param {string} id idea to delete
 */
export function deleteCurrentIdea() {
    return (dispatch, getState, axios) => {
        const { uuid } = selectCurrentIdea(getState());
        if (uuid) {
            // idea already exists (whatever its state)
            return axios.delete(`/api/ideas-workshop/ideas/${uuid}`).then(() => {
                dispatch(hideModal());
                history.push('/atelier-des-idees');
            });
        }
        dispatch(hideModal());
        history.push('/atelier-des-idees');
    };
}

export function goBackFromCurrentIdea() {
    return () => {
        history.push('/atelier-des-idees');
    };
}

export function saveCurrentIdea(ideaData) {
    return (dispatch, getState, axios) => {
        const { uuid, answers = [] } = selectCurrentIdea(getState());
        dispatch(createRequest(SAVE_CURRENT_IDEA, uuid));
        if (uuid) {
            // idea already exists (whatever its state)
            // add answer id to answers before sending
            ideaData.answers = ideaData.answers.map((answer) => {
                const { id } = answers.find(a => a.question.id === parseInt(answer.question, 10)) || {};
                return id ? { ...answer, id } : answer;
            });
            return axios
                .put(`/api/ideas-workshop/ideas/${uuid}`, ideaData)
                .then(res => res.data)
                .then((data) => {
                    dispatch(updateCurrentIdea(data));
                    dispatch(createRequestSuccess(SAVE_CURRENT_IDEA, uuid));
                })
                .catch(() => dispatch(createRequestFailure(SAVE_CURRENT_IDEA, uuid)));
        }
        return axios
            .post('/api/ideas-workshop/ideas', ideaData)
            .then(res => res.data)
            .then((data) => {
                dispatch(setCurrentIdea(data));
                dispatch(createRequestSuccess(SAVE_CURRENT_IDEA));
                // silently replace location
                window.history.replaceState(null, '', `/atelier-des-idees/note/${data.uuid}`);
            })
            .catch(() => dispatch(createRequestFailure(SAVE_CURRENT_IDEA)));
    };
}

export function saveAndPublishIdea(uuid, data) {
    return dispatch =>
        dispatch(saveIdea(uuid, data)).then((resData) => {
            if (!uuid) {
                dispatch(updateCurrentIdea({ uuid: resData.uuid }));
            }
            dispatch(publishIdea(uuid || resData.uuid)).then(() => {
                // update current idea on publish success
                dispatch(updateCurrentIdea({ status: ideaStatus.PENDING }));
            });
        });
}

export function publishCurrentIdea(ideaData) {
    return (dispatch, getState, axios) => {
        const { uuid } = selectCurrentIdea(getState());
        return dispatch(saveAndPublishIdea(uuid, ideaData));
    };
}

export function fetchGuidelines() {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_GUIDELINES));
        axios
            .get('/api/ideas-workshop/guidelines')
            .then(res => res.data)
            .then((data) => {
                dispatch(setGuidelines(data));
                dispatch(createRequestSuccess(FETCH_GUIDELINES));
            })
            .catch(() => dispatch(createRequestFailure(FETCH_GUIDELINES)));
    };
}

export function voteCurrentIdea(voteType) {
    return (dispatch, getState) => {
        const isAuthenticated = selectIsAuthenticated(getState());
        if (isAuthenticated) {
            const currentIdea = selectCurrentIdea(getState());
            const { votes_count } = currentIdea;
            const hasAlreadyVoted = votes_count.my_votes && Object.keys(votes_count.my_votes).includes(voteType);
            // TODO: improve all that below by storing all ideas in ideas reducer
            // simulate vote cancel if has already voted
            if (hasAlreadyVoted) {
                dispatch(toggleVoteCurrentIdea({ voteType }));
            }
            return dispatch(voteIdea(currentIdea.uuid, voteType, currentIdea)).then(
                (voteData) => {
                    if (!hasAlreadyVoted) {
                        // post vote success, update state data
                        dispatch(toggleVoteCurrentIdea({ voteType, voteId: voteData.id }));
                    }
                },
                () => {
                    if (hasAlreadyVoted) {
                        // cancel vote failure, toggle back state data
                        dispatch(
                            toggleVoteCurrentIdea({
                                voteType,
                                voteId: votes_count.my_votes[voteType],
                            })
                        );
                    }
                }
            );
        }
        window.location = '/connexion';
    };
}

export function postCommentToCurrentIdea(content, answerId, parentId = '') {
    return (dispatch, getState) =>
        dispatch(postComment(content, answerId, parentId)).then((newComment) => {
            if (!parentId) {
                dispatch(addThreads([{ ...newComment, comments: { total_items: 0 } }]));
                // increment answer total item
                const answer = selectCurrentIdeaAnswer(getState(), answerId);
                const updatedAnswer = {
                    ...answer,
                    threads: {
                        ...answer.threads,
                        total_items: answer.threads.total_items + 1,
                    },
                };
                dispatch(updateCurrentIdeaAnswer(answerId, updatedAnswer));
            } else {
                dispatch(addThreadComments([newComment]));
                const thread = selectThread(getState(), parentId);
                dispatch(
                    updateThread(parentId, {
                        comments: {
                            ...thread.comments,
                            total_items: thread.comments.total_items + 1,
                        },
                    })
                );
            }
        });
}

export function removeCommentFromCurrentIdea(id, parentId = '') {
    return (dispatch, getState) =>
        dispatch(deleteComment(id, parentId)).then((thread) => {
            if (parentId) {
                const parentThread = selectThread(getState(), parentId);
                // update thread parent items counter
                dispatch(
                    updateThread(parentId, {
                        comments: {
                            ...parentThread.comments,
                            total_items: parentThread.comments.total_items - 1,
                        },
                    })
                );
            } else {
                // decrement answer total item
                const answerId = thread.answer.id;
                const answer = selectCurrentIdeaAnswer(getState(), answerId);
                const updatedAnswer = {
                    ...answer,
                    threads: {
                        ...answer.threads,
                        total_items: answer.threads.total_items - 1,
                    },
                };
                dispatch(updateCurrentIdeaAnswer(answerId, updatedAnswer));
            }
        });
}

export function fetchNextAnswerThreads(answerId) {
    return (dispatch, getState) => {
        // compute page to fetch from threads data
        const state = getState();
        const answer = selectCurrentIdeaAnswer(state, answerId);
        // pading data
        const pagingData = selectAnswerThreadsPagingData(state, answerId);
        const page = pagingData ? pagingData.current_page + 1 : 2;
        return dispatch(fetchThreads({ 'answer.id': answerId, page, limit: 3 })).then(({ items, metadata }) => {
            // add threads to collection
            dispatch(addThreads(items));
            // update current answer total items
            const updatedAnswer = {
                ...answer,
                threads: { ...answer.threads, total_items: metadata.total_items },
            };
            dispatch(updateCurrentIdeaAnswer(answerId, updatedAnswer));
            // update paging data
            dispatch(setThreadPagingData(answerId, metadata));
        });
    };
}
