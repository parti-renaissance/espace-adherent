import { updateStatic } from '../actions/static';

export function fetchThemes() {
    return (dispatch, getState, axios) =>
        axios
            .get('/api/themes')
            .then(res => res.data)
            .then(data => dispatch(updateStatic({ themes: data.items })));
}

export function fetchCategories() {
    return (dispatch, getState, axios) =>
        axios
            .get('/api/categories')
            .then(res => res.data)
            .then(data => dispatch(updateStatic({ categories: data.items })));
}

export function fetchCommittees() {
    return (dispatch, getState, axios) =>
        axios
            .get('/api/committees/me')
            .then(res => res.data)
            .then(data => dispatch(updateStatic({ committees: data })));
}

export function fetchNeeds() {
    return (dispatch, getState, axios) =>
        axios
            .get('/api/needs')
            .then(res => res.data)
            .then(data => dispatch(updateStatic({ needs: data.items })));
}
