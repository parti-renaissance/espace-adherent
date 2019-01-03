import { getMyIdeas, getMyIdeasMetadata } from '../reducers/myIdeas';

export const selectMyIdeas = state => getMyIdeas(state.ideas);
export const selectMyIdeasMetadata = state => getMyIdeasMetadata(state.ideas);
