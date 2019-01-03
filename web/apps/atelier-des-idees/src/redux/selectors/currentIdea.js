import { getCurrentIdea, getGuidelines } from '../reducers/currentIdea';

export const selectCurrentIdea = state => getCurrentIdea(state.currentIdea);
export const selectGuidelines = state => getGuidelines(state.currentIdea);
