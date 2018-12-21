import { fetchIdeas } from './ideas';
import { ideaStatus } from '../../constants/api';

export function initHomePage() {
    const params = { limit: 5, order_desc: true };
    return dispatch =>
        Promise.all([
            dispatch(fetchIdeas(ideaStatus.FINALIZED, params)),
            dispatch(fetchIdeas(ideaStatus.PENDING, params)),
        ]);
}
