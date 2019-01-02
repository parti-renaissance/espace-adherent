import { ideaStatus } from '../../constants/api';
import { fetchIdeas, fetchIdea } from './ideas';
import { fetchConsultationPinned } from './pinned';
import { fetchReports } from './reports';
import { fetchAuthUser } from './auth';
import { fetchGuidelines } from './currentIdea';

export function initApp() {
    return dispatch => dispatch(fetchAuthUser());
}

export function initHomePage() {
    const params = {
        limit: 5,
        'order[\'created_at\']': 'DESC',
    };
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
    const params = {
        limit: 10,
        'order[\'created_at\']': 'DESC',
    };
    return dispatch => dispatch(fetchIdeas(ideaStatus.PENDING, params, true));
}

export function initConsultPage() {
    const params = {
        limit: 10,
        'order[\'created_at\']': 'DESC',
    };
    return dispatch => dispatch(fetchIdeas(ideaStatus.FINALIZED, params, true));
}

export function initIdeaPage(id) {
    return dispatch => dispatch(fetchIdea(id));
}

export function initCreateIdeaPage() {
    return dispatch => dispatch(fetchGuidelines());
}
