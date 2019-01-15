import { updateStatic } from '../actions/static';

export function fetchThemes() {
    return (dispatch, getState, axios) =>
        axios
            .get('/api/ideas-workshop/themes')
            .then(res => res.data)
            .then(data => dispatch(updateStatic({ themes: data.items })));
}

export function fetchCategories() {
    return (dispatch, getState, axios) =>
        axios
            .get('/api/ideas-workshop/categories')
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
            .get('/api/ideas-workshop/needs')
            .then(res => res.data)
            .then(data => dispatch(updateStatic({ needs: data.items })));
}

export function fetchFlagReasons() {
    return (dispatch, getState, axios) =>
        axios
            .get('/api/report/reasons')
            .then(res => res.data)
            .then(data => dispatch(updateStatic({ reasons: data })));
}

export function fetchStaticData() {
    return dispatch =>
        Promise.all([
            dispatch(fetchThemes()),
            dispatch(fetchCategories()),
            dispatch(fetchNeeds()),
            dispatch(fetchCommittees()),
            dispatch(fetchFlagReasons()),
        ]);
}
