import {
    removeThread,
    toggleApproveThread,
    addThreadComments,
    updateThread,
    setThreadPagingData,
    removeThreadComment,
    toggleApproveThreadComment,
} from '../actions/threads';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { POST_THREAD, POST_THREAD_COMMENT } from '../constants/actionTypes';
import { selectThread, selectThreadPagingData, selectThreadComment } from '../selectors/threads';

export function approveComment(id, parentId = '') {
    return (dispatch, getState, axios) => {
        let type = 'threads';
        if (parentId) {
            type = 'thread_comments';
        }
        const comment = parentId ? selectThreadComment(getState(), id) : selectThread(getState(), id);
        // simulate toggle
        if (parentId) {
            dispatch(toggleApproveThreadComment(id));
        } else {
            dispatch(toggleApproveThread(id));
        }
        return (
            axios
                .put(`/api/ideas-workshop/${type}/${id}/approval-toggle`, { approved: !comment.approved })
                // toggle back if error
                .catch(() => {
                    if (parentId) {
                        dispatch(toggleApproveThreadComment(id));
                    } else {
                        dispatch(toggleApproveThread(id));
                    }
                })
        );
    };
}

export function deleteComment(id, parentId = '') {
    return (dispatch, getState, axios) => {
        let type = 'threads';
        if (parentId) {
            type = 'thread_comments';
        }
        return axios
            .delete(`/api/ideas-workshop/${type}/${id}`)
            .then(() => {
                if (parentId) {
                    dispatch(removeThreadComment(id));
                } else {
                    dispatch(removeThread(id));
                }
            })
            .catch((error) => {
                throw error;
            });
    };
}

export function postComment(content, answerId, parentId = '') {
    return (dispatch, getState, axios) => {
        let type = 'threads';
        const fetchType = parentId ? POST_THREAD_COMMENT : POST_THREAD;
        const fetchId = `${answerId}${parentId ? `_${parentId}` : ''}`;
        const body = { content };
        if (parentId) {
            type = 'thread_comments';
            body.thread = parentId;
        } else {
            body.answer = answerId;
        }
        dispatch(createRequest(fetchType, fetchId));
        return axios
            .post(`/api/ideas-workshop/${type}`, body)
            .then(res => res.data)
            .then((thread) => {
                dispatch(createRequestSuccess(fetchType, fetchId));
                return thread;
            })
            .catch((error) => {
                dispatch(createRequestFailure(fetchType, fetchId));
                throw error;
            });
    };
}

export function reportComment(reportData, id, parentId = '') {
    return (dispatch, getState, axios) => {
        const reportType = parentId ? 'atelier-des-idees-reponses' : 'atelier-des-idees-commentaires';
        return axios.post(`/api/report/${reportType}/${id}`, reportData);
    };
}

export function fetchThreads(params = {}) {
    return (dispatch, getState, axios) =>
        axios
            .get('/api/ideas-workshop/threads', { params })
            .then(res => res.data)
            .catch((error) => {
                throw error;
            });
}

export function fetchThreadComments(threadId, params = {}) {
    return (dispatch, getState, axios) =>
        axios
            .get(`/api/ideas-workshop/threads/${threadId}/comments`, { params })
            .then(res => res.data)
            .catch((error) => {
                throw error;
            });
}

export function fetchNextThreadComments(threadId) {
    return (dispatch, getState) => {
        // pading data
        const pagingData = selectThreadPagingData(getState(), threadId);
        const page = pagingData ? pagingData.current_page + 1 : 2;
        dispatch(fetchThreadComments(threadId, { page, limit: 3 })).then(({ items, metadata }) => {
            // add comments to collection
            dispatch(addThreadComments(items.map(comment => ({ ...comment, thread: { uuid: threadId } }))));
            // update parent thread total items
            const thread = selectThread(getState(), threadId);
            dispatch(
                updateThread(threadId, {
                    comments: { ...thread.comments, total_items: metadata.total_items },
                })
            );
            // update paging data
            dispatch(setThreadPagingData(threadId, metadata));
        });
    };
}
