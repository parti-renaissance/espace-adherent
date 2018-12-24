import { HIDE_CONSULTATION_PINNED, SHOW_CONSULTATION_PINNED } from '../constants/actionTypes';

const initialState = { consultation: { show: false, data: {} } };

function pinnedReducer(state = initialState, action) {
    const { type, payload } = action;
    switch (type) {
    case SHOW_CONSULTATION_PINNED: {
        const { data } = payload;
        return { ...state, consultation: { show: true, data } };
    }
    case HIDE_CONSULTATION_PINNED:
        return { ...state, consultation: initialState.consultation };
    default:
        return state;
    }
}

export default pinnedReducer;

export const getShowConsultationPinned = state => state.consultation.show;
export const getConsultationPinned = state => state.consultation.data;
