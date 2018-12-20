import { fetchIdeas } from './ideas';

export function initHomePage() {
    const params = { limit: 5, order_desc: true };
    return dispatch =>
        Promise.all([dispatch(fetchIdeas('published', params)), dispatch(fetchIdeas('pending', params))]);
}
