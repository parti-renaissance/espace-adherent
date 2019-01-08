import { setThreads, removeThread, toggleApproveThread } from '../actions/threads';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { POST_THREAD } from '../constants/actionTypes';

export function fetchIdeaThreads(id) {
    return (dispatch, getState, axios) =>
        axios
            .get(`/api/threads?answer.idea.uuid=${id}`)
            .then(res => res.data)
            .then(data => dispatch(setThreads(data)));
}

export function approveComment(id, parentId = '') {
    return (dispatch, getState, axios) => {
        let type = 'threads';
        if (parentId) {
            type = 'thread_comments';
        }
        dispatch(toggleApproveThread(id));
        return axios.put(`/api/${type}/${id}/approve`).catch(() => dispatch(toggleApproveThread(id)));
    };
}

export function deleteComment(id, parentId = '') {
    return (dispatch, getState, axios) => {
        let type = 'threads';
        if (parentId) {
            type = 'thread_comments';
        }
        return axios.delete(`/api/${type}/${id}`).then(() => dispatch(removeThread(id)));
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
