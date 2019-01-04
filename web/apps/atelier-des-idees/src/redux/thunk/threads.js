import { setCurrentIdeaThreads } from '../actions/currentIdea';

export function fetchIdeaThreads(id) {
    return (dispatch, getState, axios) =>
        axios
            .get(`/api/threads/${id}/comments`)
            .then(res => res.data)
            .then(data => dispatch(setCurrentIdeaThreads(data.items)));
}

export function approveComment(id, parentId = '') {
    return (dispatch, getState, axios) => {
        let type = 'threads';
        if (parentId) {
            type = 'thread_comments';
        }
        return axios.put(`/api/${type}/${id}/approve`);
    };
}

export function deleteComment(id, parentId = '') {
    return (dispatch, getState, axios) => {
        let type = 'threads';
        if (parentId) {
            type = 'thread_comments';
        }
        return axios.delete(`/api/${type}/${id}`);
    };
}

export function postComment(content, answerId, parentId = '') {
    return (dispatch, getState, axios) => {
        // TODO: use answerId
        let type = 'threads';
        const body = { content };
        if (parentId) {
            type = 'thread_comments';
            body.thread = parentId;
        }
        return axios.post(`/api/${type}`, body);
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
