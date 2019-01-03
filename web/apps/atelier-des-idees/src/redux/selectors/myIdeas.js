import { getMyIdeas, getMyIdeasMetadata } from '../reducers/myIdeas';

export const selectMyIdeas = state => getMyIdeas(state.myIdeas);
export const selectMyIdeasMetadata = state => getMyIdeasMetadata(state.myIdeas);
