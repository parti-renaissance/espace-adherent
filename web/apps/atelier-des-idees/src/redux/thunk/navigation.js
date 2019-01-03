import { ideaStatus } from '../../constants/api';
import { fetchIdeas, fetchIdea, fetchUserIdeas, fetchUserContributions } from './ideas';
import { fetchConsultationPinned } from './pinned';
import { fetchReports } from './reports';
import { fetchAuthUser } from './auth';
import { fetchGuidelines } from './currentIdea';
import { fetchThemes, fetchCategories, fetchCommittees, fetchNeeds } from './static';

export function initApp() {
    return dispatch => dispatch(fetchAuthUser());
}

export function initHomePage() {
    const params = {
        limit: 5,
        "order['created_at']": 'DESC',
    };
    return dispatch =>
        Promise.all([
            // consultation pinned
            dispatch(fetchConsultationPinned()),
            // ideas
            dispatch(fetchIdeas(ideaStatus.FINALIZED, params)),
            dispatch(fetchIdeas(ideaStatus.PENDING, params)),
            // user ideas
            dispatch(fetchUserIdeas()),
            // user contributions
            dispatch(fetchUserContributions()),
            // reports
            dispatch(fetchReports()),
        ]);
}

export function initContributePage() {
    const params = {
        limit: 10,
        "order['created_at']": 'DESC',
    };
    return dispatch => dispatch(fetchIdeas(ideaStatus.PENDING, params, true));
}

export function initConsultPage() {
    const params = {
        limit: 10,
        "order['created_at']": 'DESC',
    };
    return dispatch => dispatch(fetchIdeas(ideaStatus.FINALIZED, params, true));
}

export function initIdeaPageBase() {
    return dispatch => {
        dispatch(fetchGuidelines());
        dispatch(fetchThemes());
        dispatch(fetchCategories());
        dispatch(fetchNeeds());
        dispatch(fetchCommittees());
    };
}

export function initIdeaPage(id) {
    return async dispatch => {
        await dispatch(fetchIdea(id));
        dispatch(initIdeaPageBase());
    };
}

export function initCreateIdeaPage() {
    return dispatch => dispatch(initIdeaPageBase());
}
