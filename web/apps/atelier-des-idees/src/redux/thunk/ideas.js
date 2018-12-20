import { addIdeas } from '../actions/ideas';

const HOST = process.env.REACT_APP_EM_API_HOST;

export function fetchIdeas(status) {
    return (dispatch, getState, axios) =>
        axios
            .get('/api/ideas', { params: { status } })
            .then(res => res.data)
            .then(ideas => dispatch(addIdeas(ideas)));
}
