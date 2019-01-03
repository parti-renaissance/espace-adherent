import { FETCH_CONSULTATION_PINNED } from '../constants/actionTypes';
import {
    createRequest,
    createRequestSuccess,
    createRequestFailure,
} from '../actions/loading';
import { showConsultationPinned } from '../actions/pinned';

export function fetchConsultationPinned() {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_CONSULTATION_PINNED));
        return axios
            .get('/api/consultations')
            .then(res => res.data)
            .then(({ items, metadata }) => {
                if (items.length) {
                    dispatch(showConsultationPinned(items[0]));
                }
                dispatch(createRequestSuccess(FETCH_CONSULTATION_PINNED));
            })
            .catch((error) => {
                dispatch(createRequestFailure(FETCH_CONSULTATION_PINNED));
            });
    };
}
