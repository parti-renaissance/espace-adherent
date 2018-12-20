import { getLoadingState } from '../reducers/loading';

export const selectLoadingState = (state, requestName) => getLoadingState(state.loading, requestName);
export const selectLoadingStates = (state, requestNames) =>
    // returns true only when all actions are not loading
    requestNames.some(requestName => getLoadingState(state.loading, requestName));
