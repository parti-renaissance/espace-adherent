function fetchPostsRequest() {
    return {
        type: 'FETCH_REQUEST',
    };
}

function fetchPostsSuccess(payload) {
    return {
        type: 'FETCH_SUCCESS',
        payload,
    };
}

function fetchPostsError() {
    return {
        type: 'FETCH_ERROR',
    };
}
