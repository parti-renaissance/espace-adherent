import { fetchIdeas } from './ideas';
import { ideaStatus } from '../../constants/api';

export function initHomePage() {
    const params = { limit: 5, 'order[\'created_at\']': 'DESC' };
    return dispatch =>
        Promise.all([
            dispatch(fetchIdeas(ideaStatus.FINALIZED, params)),
            dispatch(fetchIdeas(ideaStatus.PENDING, params)),
        ]);
}
