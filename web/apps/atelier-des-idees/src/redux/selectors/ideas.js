import { getIdeas, getIdeasWithStatus, getIdeasMetadata } from '../reducers/ideas';

export const selectIdeas = state => getIdeas(state.ideas);
export const selectIdeasMetadata = state => getIdeasMetadata(state.ideas);
export const selectIdeasWithStatus = (state, status) => getIdeasWithStatus(state.ideas, status);
