import {
    getShowConsultationPinned,
    getConsultationPinned,
} from '../reducers/pinned';

export const selectShowConsultationPinned = state =>
    getShowConsultationPinned(state.pinned);
export const selectConsultationPinned = state =>
    getConsultationPinned(state.pinned);
