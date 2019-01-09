import { ideaStatus } from '../../constants/api';
import history from '../../history';
import { SAVE_CURRENT_IDEA, FETCH_GUIDELINES, VOTE_CURRENT_IDEA } from '../constants/actionTypes';
import { saveAndPublishIdea } from '../thunk/ideas';
import { postComment, fetchThreads } from '../thunk/threads';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { selectIsAuthenticated } from '../selectors/auth';
import { selectCurrentIdea, selectCurrentIdeaAnswer } from '../selectors/currentIdea';
import { selectAnswerThreads } from '../selectors/threads';
import { setCurrentIdea, updateCurrentIdea, setGuidelines, toggleVoteCurrentIdea } from '../actions/currentIdea';
import { addThreads } from '../actions/threads';
import { hideModal } from '../actions/modal';

/**
 * Delete an idea
 * @param {string} id idea to delete
 */
export function deleteCurrentIdea() {
    return (dispatch, getState, axios) => {
        const { id } = selectCurrentIdea(getState());
        if (id) {
            // idea already exists (whatever its state)
            return axios.delete(`/api/ideas/${id}`).then(() => {
                dispatch(hideModal());
                history.push('/atelier-des-idees');
            });
        }
        dispatch(hideModal());
        history.push('/atelier-des-idees');
    };
}

export function goBackFromCurrentIdea() {
    return (dispatch, getState) => {
        const { status } = selectCurrentIdea(getState());
        switch (status) {
        case ideaStatus.FINALIZED:
            history.push('/atelier-des-idees/consulter');
            break;
        case ideaStatus.PENDING:
            history.push('/atelier-des-idees/contribuer');
            break;
        case ideaStatus.DRAFT:
        default:
            history.push('/atelier-des-idees/proposer');
        }
    };
}

export function saveCurrentIdea(ideaData) {
    return (dispatch, getState, axios) => {
        const { uuid, answers } = selectCurrentIdea(getState());
        dispatch(createRequest(SAVE_CURRENT_IDEA, uuid));
        if (uuid) {
            // idea already exists (whatever its state)
            // add answer id to answers before sending
            ideaData.answers = ideaData.answers.map((answer) => {
                const { id } = answers.find(a => a.question.id === parseInt(answer.question, 10)) || {};
                return id ? { ...answer, id } : answer;
            });
            return axios
                .put(`/api/ideas/${uuid}`, ideaData)
                .then(res => res.data)
                .then((data) => {
                    dispatch(updateCurrentIdea(data));
                    dispatch(createRequestSuccess(SAVE_CURRENT_IDEA, uuid));
                })
                .catch(() => dispatch(createRequestFailure(SAVE_CURRENT_IDEA, uuid)));
        }
        return axios
            .post('/api/ideas', ideaData)
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
            .get('/api/guidelines')
            .then(res => res.data)
            .then((data) => {
                dispatch(setGuidelines(data));
                dispatch(createRequestSuccess(FETCH_GUIDELINES));
            })
            .catch(() => dispatch(createRequestFailure(FETCH_GUIDELINES)));
    };
}

export function voteCurrentIdea(vote) {
    return (dispatch, getState, axios) => {
        const isAuthenticated = selectIsAuthenticated(getState());
        if (isAuthenticated) {
            const { uuid } = selectCurrentIdea(getState());
            dispatch(toggleVoteCurrentIdea(vote));
            dispatch(createRequest(VOTE_CURRENT_IDEA, uuid));
            const requestBody = {
                method: 'POST',
                url: '/api/votes',
                data: { idea: uuid, type: vote },
            };
            return axios(requestBody)
                .then(res => res.data)
                .then(() => {
                    dispatch(createRequestSuccess(VOTE_CURRENT_IDEA, uuid));
                })
                .catch(() => {
                    dispatch(toggleVoteCurrentIdea(vote));
                    dispatch(createRequestFailure(VOTE_CURRENT_IDEA, uuid));
                });
        }
        window.location = '/connexion';
    };
}

export function postCommentToCurrentIdea(content, answerId, parentId = '') {
    // TODO: handle parentId
    return dispatch =>
        dispatch(postComment(content, answerId, parentId))
            .then(thread => dispatch(addThreads([thread])))
            // TODO: remove catch
            .catch(
                thread =>
                    dispatch(
                        addThreads([
                            {
                                answer: {
                                    id: answerId,
                                },
                                content,
                                author: {
                                    uuid: '0000',
                                    first_name: 'Adrien',
                                    last_name: 'Casanova',
                                },
                                created_at: new Date().toISOString(),
                                uuid: '909920830',
                            },
                        ])
                    )
                // TODO: update current answer total items
            );
}

export function fetchNextAnswerThreads(answerId) {
    return (dispatch, getState) => {
        // compute page to fetch from threads data
        const answer = selectCurrentIdeaAnswer(getState(), answerId);
        const answerThreads = selectAnswerThreads(getState(), answerId);
        const { threads } = answer;
        const page = threads.total_items / answerThreads.length;
        return dispatch(fetchThreads({ 'answer.id': answerId, page })).then(({ items, metadata }) => {
            dispatch(addThreads(items));
            // dispatch(
            //     addThreads([
            //         {
            //             answer: {
            //                 id: answerId,
            //             },
            //             content: 'New comment',
            //             author: {
            //                 uuid: '0000',
            //                 first_name: 'Adrien',
            //                 last_name: 'Casanova',
            //             },
            //             created_at: new Date().toISOString(),
            //             uuid: '909920830',
            //         },
            //         {
            //             answer: {
            //                 id: answerId,
            //             },
            //             content: 'New comment',
            //             author: {
            //                 uuid: '0000',
            //                 first_name: 'Adrien',
            //                 last_name: 'Casanova',
            //             },
            //             created_at: new Date().toISOString(),
            //             uuid: '909920830',
            //         },
            //     ])
            // );
            // TODO: update current answer total items
        });
    };
}
