import { getCurrentIdea, getGuidelines, getCurrentIdeaThread } from '../reducers/currentIdea';

export const selectCurrentIdea = state => getCurrentIdea(state.currentIdea);
export const selectGuidelines = state => getGuidelines(state.currentIdea);
export const selectCurrentIdeaThread = (state, answerId) => getCurrentIdeaThread(state.currentIdea);
