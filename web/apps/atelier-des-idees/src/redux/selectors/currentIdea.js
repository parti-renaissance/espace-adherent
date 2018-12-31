import { getCurrentIdea } from '../reducers/currentIdea';

export const selectCurrentIdea = state => getCurrentIdea(state.currentIdea);
