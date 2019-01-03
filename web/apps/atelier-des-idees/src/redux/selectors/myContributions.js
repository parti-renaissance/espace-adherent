import {
    getMyContributions,
    getMyContributionsMetadata,
} from '../reducers/myContributions';

export const selectMyContributions = state => getMyContributions(state.ideas);
export const selectMyContributionsMetadata = state =>
    getMyContributionsMetadata(state.ideas);
