import { getLoadingState } from '../reducers/loading';

export const selectLoadingState = (state, requestName, id = '') =>
    getLoadingState(state.loading, `${requestName}${id ? `_${id}` : ''}`);
export const selectLoadingStates = (state, requestNames) =>
    // returns true only when all actions are not loading
    requestNames.some(requestName => getLoadingState(state.loading, requestName));
