import { FETCH_IDEA_THREADS } from '../constants/actionTypes';
import { setCurrentIdeaThreads } from '../actions/currentIdea';

export function fetchIdeaThreads(id) {
    return (dispatch, getState, axios) =>
        axios
            .get(`/api/threads/${id}/comments`)
            .then(res => res.data)
            .then(data => dispatch(setCurrentIdeaThreads(data.items)));
}
