import { getCurrentIdea, getGuidelines, getCurrentIdeaAnswer } from '../reducers/currentIdea';

export const selectCurrentIdea = state => getCurrentIdea(state.currentIdea);
export const selectCurrentIdeaAnswer = (state, answerId) => getCurrentIdeaAnswer(state.currentIdea, answerId);
export const selectGuidelines = state => getGuidelines(state.currentIdea);
