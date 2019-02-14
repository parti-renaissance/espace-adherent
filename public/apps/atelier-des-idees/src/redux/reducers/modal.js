import { SHOW_MODAL, HIDE_MODAL } from '../constants/actionTypes';

const initialState = {
    modalType: null,
    modalProps: {},
    isOpen: false,
};

function modalReducer(state = initialState, action) {
    const { type, payload } = action;
    switch (type) {
    case SHOW_MODAL:
        return {
            modalType: payload.modalType,
            modalProps: payload.modalProps,
            isOpen: true,
        };
    case HIDE_MODAL:
        return initialState;
    default:
        return state;
    }
}

export default modalReducer;

// getters
export const getModalData = state => state;
