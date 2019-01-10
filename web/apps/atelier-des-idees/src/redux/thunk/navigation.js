import { ideaStatus } from '../../constants/api';
import { fetchIdeas, fetchIdea, fetchUserIdeas, fetchUserContributions } from './ideas';
import { fetchConsultationPinned } from './pinned';
import { fetchReports } from './reports';
import { fetchAuthUser } from './auth';
import { fetchGuidelines } from './currentIdea';
import { fetchStaticData } from './static';
import { setCurrentIdea } from '../actions/currentIdea';

export function initApp() {
    return dispatch =>
        Promise.all([
            dispatch(fetchAuthUser()).then(() => dispatch(fetchUserIdeas())),
            dispatch(fetchStaticData()),
            dispatch(fetchUserContributions()),
        ]);
}

export function initHomePage() {
    const params = {
        limit: 5,
        "order['publishedAt']": 'DESC',
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
        "order['publishedAt']": 'DESC',
    };
    return dispatch => dispatch(fetchIdeas(ideaStatus.PENDING, params, true));
}

export function initConsultPage() {
    const params = {
        limit: 10,
        "order['publishedAt']": 'DESC',
    };
    return dispatch => dispatch(fetchIdeas(ideaStatus.FINALIZED, params, true));
}

export function initIdeaPageBase() {
    return dispatch => dispatch(fetchGuidelines());
}

export function initIdeaPage(id) {
    return async dispatch => {
        await dispatch(fetchIdea(id));
        dispatch(initIdeaPageBase());
    };
}

export function initCreateIdeaPage() {
    return dispatch => {
        dispatch(initIdeaPageBase());
        dispatch(setCurrentIdea());
    };
}
