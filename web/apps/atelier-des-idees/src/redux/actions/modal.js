import { action } from '../helpers/actions';
import { SHOW_MODAL, HIDE_MODAL } from '../constants/actionTypes';

export const showModal = (modalType, modalProps = {}) =>
    action(SHOW_MODAL, { modalType, modalProps });
export const hideModal = () => action(HIDE_MODAL);
