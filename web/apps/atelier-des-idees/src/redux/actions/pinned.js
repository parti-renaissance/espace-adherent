import { action } from '../helpers/actions';
import { HIDE_CONSULTATION_PINNED, SHOW_CONSULTATION_PINNED } from '../constants/actionTypes';

export const hideConsultationPinned = () => action(HIDE_CONSULTATION_PINNED);
export const showConsultationPinned = data => action(SHOW_CONSULTATION_PINNED, { data });
