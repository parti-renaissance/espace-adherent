import { addIdeas } from '../actions/ideas';

const HOST = process.env.REACT_APP_EM_API_HOST;

export function fetchIdeas(status, params = {}) {
    return (dispatch, getState, axios) =>
        axios
            .get('/api/ideas', { params: { status, ...params } })
            .then(res => res.data)
            .then(ideas => dispatch(addIdeas(ideas)));
}
