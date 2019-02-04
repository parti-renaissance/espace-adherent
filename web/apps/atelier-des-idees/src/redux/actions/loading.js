import { RESET_LOADING, RESET_LOADING_STATE } from '../constants/actionTypes';
import { action } from '../helpers/actions';

export const createRequest = (requestName, id = '') => action(requestName.REQUEST, { id });
export const createRequestSuccess = (requestName, id = '') => action(requestName.SUCCESS, { id });
export const createRequestFailure = (requestName, id = '') => action(requestName.FAILURE, { id });
export const resetLoading = () => action(RESET_LOADING);
export const resetLoadingState = requestName => action(RESET_LOADING_STATE, { requestName });
