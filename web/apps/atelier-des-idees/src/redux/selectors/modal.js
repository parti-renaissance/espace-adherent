import { getModalData } from '../reducers/modal';

export const selectModalData = state => getModalData(state.modal);
