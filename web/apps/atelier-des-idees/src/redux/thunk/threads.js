import { removeThread, toggleApproveThread } from '../actions/threads';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { POST_THREAD } from '../constants/actionTypes';
import { selectThread } from '../selectors/threads';

export function approveComment(id, parentId = '') {
    return (dispatch, getState, axios) => {
        let type = 'threads';
        if (parentId) {
            type = 'thread_comments';
        }
        // TODO: handle threadcomment
        const thread = selectThread(getState(), id);
        // simulate toggle
        dispatch(toggleApproveThread(id));
        return (
            axios
                .put(`/api/${type}/${id}/approval-toggle`, { approved: !thread.approved })
                // toggle back if error
                .catch(() => dispatch(toggleApproveThread(id)))
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
            .delete(`/api/${type}/${id}`)
            .then(() => dispatch(removeThread(id)))
            .catch((error) => {
                throw error;
            });
    };
}

export function postComment(content, answerId, parentId = '') {
    return (dispatch, getState, axios) => {
        let type = 'threads';
        const body = { content };
        if (parentId) {
            type = 'thread_comments';
            body.thread = parentId;
        } else {
            body.answer = answerId;
        }
        dispatch(createRequest(POST_THREAD, answerId));
        return axios
            .post(`/api/${type}`, body)
            .then(res => res.data)
            .then(() => dispatch(createRequestSuccess(POST_THREAD, answerId)))
            .catch((error) => {
                dispatch(createRequestFailure(POST_THREAD, answerId));
                throw error;
            });
    };
}

export function reportComment(id, parentId = '') {
    return (dispatch, getState, axios) => {
        let type = 'threads';
        if (parentId) {
            type = 'thread_comments';
        }
        return axios.put(`/api/${type}/${id}/report`);
    };
}

export function fetchThreads(params = {}) {
    return (dispatch, getState, axios) =>
        axios
            .get('/api/threads', { params })
            .then(res => res.data)
            .catch((error) => {
                throw error;
            });
}
