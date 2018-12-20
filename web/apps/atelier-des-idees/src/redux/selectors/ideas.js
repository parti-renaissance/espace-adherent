import { getIdeas } from '../reducers/ideas';

export const selectIdeas = state => getIdeas(state.ideas);
