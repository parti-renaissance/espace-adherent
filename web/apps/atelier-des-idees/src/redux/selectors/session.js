import { getVisitedIdeas } from '../reducers/session';

export const selectVisitedIdeas = state => getVisitedIdeas(state.session);
