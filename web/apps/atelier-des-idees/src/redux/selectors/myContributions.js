import { getMyContributions, getMyContributionsMetadata } from '../reducers/myContributions';

export const selectMyContributions = state => getMyContributions(state.myContributions);
export const selectMyContributionsMetadata = state => getMyContributionsMetadata(state.myContributions);
