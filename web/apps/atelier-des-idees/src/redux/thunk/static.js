export function fetchThemes() {
    return (dispatch, getState, axios) => axios.get('/api/themes').then(res => res.data);
}

export function fetchCategories() {
    return (dispatch, getState, axios) => axios.get('/api/categories').then(res => res.data);
}

export function fetchCommittees() {
    return (dispatch, getState, axios) => axios.get('/api/committees/me').then(res => res.data);
}

export function fetchNeeds() {
    return (dispatch, getState, axios) => axios.get('/api/needs').then(res => res.data);
}
