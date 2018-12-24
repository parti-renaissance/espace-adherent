import { fetchIdeas } from './ideas';
import { fetchConsultationPinned } from './pinned';
import { fetchReports } from './reports';
import { ideaStatus } from '../../constants/api';

export function initHomePage() {
    const params = { limit: 5, 'order[\'created_at\']': 'DESC' };
    return dispatch =>
        Promise.all([
            // consultation pinned
            dispatch(fetchConsultationPinned()),
            // ideas
            dispatch(fetchIdeas(ideaStatus.FINALIZED, params)),
            dispatch(fetchIdeas(ideaStatus.PENDING, params)),
            // reports
            dispatch(fetchReports()),
        ]);
}

export function initContributePage() {
    const params = { limit: 10, 'order[\'created_at\']': 'DESC' };
    return dispatch => dispatch(fetchIdeas(ideaStatus.FINALIZED, params));
}
