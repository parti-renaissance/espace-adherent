import axios from 'axios';

export function fetchPostsWithRedux() {
    return (dispatch) => {
        dispatch(fetchPostsRequest());
        return fetchPosts().then(([response, json]) => {
            if (200 === response.status) {
                dispatch(fetchPostsSuccess(json));
            } else {
                dispatch(fetchPostsError());
            }
        });
    };
}

export function fetchPosts() {
    const URL = 'https://jsonplaceholder.typicode.com/posts';
    return axios(URL, { method: 'GET' }).then(response => Promise.all([response, response.json()]));
}
