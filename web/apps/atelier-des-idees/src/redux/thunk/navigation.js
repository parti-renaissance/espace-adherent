import { fetchIdea, fetchUserIdeas, fetchFinalizedIdeas, fetchPendingIdeas, fetchUserContributions } from './ideas';
import { setIdeas } from '../actions/ideas';
import { fetchConsultationPinned } from './pinned';
import { fetchReports } from './reports';
import { fetchAuthUser } from './auth';
import { fetchGuidelines } from './currentIdea';
import { fetchStaticData } from './static';
import { setCurrentIdea } from '../actions/currentIdea';
import { addVisitedIdea } from '../actions/session';

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
        page_size: 5,
        'order[publishedAt]': 'DESC',
    };
    return dispatch =>
        Promise.all([
            // consultation pinned
            dispatch(fetchConsultationPinned()),
            // ideas
            // flush ideas reducer
            dispatch(setIdeas()),
            dispatch(fetchPendingIdeas(params)),
            dispatch(fetchFinalizedIdeas(params)),
            // reports
            dispatch(fetchReports()),
        ]);
}

export function initIdeaPageBase() {
    return dispatch => dispatch(fetchGuidelines());
}

export function initIdeaPage(id) {
    return async dispatch => {
        // reset current idea
        dispatch(setCurrentIdea());
        return dispatch(fetchIdea(id)).then(() => {
            dispatch(initIdeaPageBase());
            dispatch(addVisitedIdea(id));
        });
    };
}

export function initCreateIdeaPage() {
    return dispatch => {
        dispatch(initIdeaPageBase());
    };
}
