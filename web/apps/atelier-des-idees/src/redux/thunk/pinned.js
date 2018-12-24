import { FETCH_CONSULTATION_PINNED } from '../constants/actionTypes';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { showConsultationPinned } from '../actions/pinned';

const consultationMock = {
    title: 'Ceci est le titre',
    link: 'www.google.fr',
    calendar: 'Du 24 janvier au 25 Février 2019.',
    duration: '2 min.',
};

export function fetchConsultationPinned() {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_CONSULTATION_PINNED));
        return (
            axios
                .get('/api/consultation-pinned')
                .then(res => res.data)
                .then((data) => {
                    dispatch(showConsultationPinned(data));
                    dispatch(createRequestSuccess(FETCH_CONSULTATION_PINNED));
                })
                .catch((error) => {
                    dispatch(createRequestFailure(FETCH_CONSULTATION_PINNED));
                })
                // TODO: remove finally when endpoint is up
                .finally(() => dispatch(showConsultationPinned(consultationMock)))
        );
    };
}
