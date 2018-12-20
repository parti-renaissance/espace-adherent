import { fetchIdeas } from './ideas';

export function initHomePage() {
    return dispatch => Promise.all([dispatch(fetchIdeas('published')), dispatch(fetchIdeas('pending'))]);
}
