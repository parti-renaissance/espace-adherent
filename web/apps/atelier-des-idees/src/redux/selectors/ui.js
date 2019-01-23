import { getShowHeader } from '../reducers/ui';

export const selectShowHeader = state => getShowHeader(state.ui);
