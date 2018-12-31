import { ideaStatus } from '../../constants/api';
import { push } from 'connected-react-router';
import { SAVE_CURRENT_IDEA } from '../constants/actionTypes';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { selectCurrentIdea } from '../selectors/currentIdea';
import { setCurrentIdea, updateCurrentIdea } from '../actions/currentIdea';
import { hideModal } from '../actions/modal';

/**
 * Delete an idea
 * @param {string} id idea to delete
 */
export function deleteCurrentIdea() {
    return (dispatch, getState, axios) => {
        const { id } = selectCurrentIdea(getState());
        if (id) {
            // idea already exists (whatever its state)
            return axios.delete(`/api/ideas/${id}`).then(() => {
                dispatch(hideModal());
                dispatch(push('/atelier-des-idees'));
            });
        }
        dispatch(hideModal());
        return dispatch(push('/atelier-des-idees'));
    };
}

export function goBackFromCurrentIdea() {
    return (dispatch, getState) => {
        const { status } = selectCurrentIdea(getState());
        switch (status) {
        case ideaStatus.FINALIZED:
            return dispatch(push('/atelier-des-idees/consulter'));
        case ideaStatus.PENDING:
            return dispatch(push('/atelier-des-idees/contribuer'));
        case ideaStatus.DRAFT:
        default:
            return dispatch(push('/atelier-des-idees/proposer'));
        }
    };
}

export function saveCurrentIdea(ideaData) {
    return (dispatch, getState, axios) => {
        const { id } = selectCurrentIdea(getState());
        dispatch(createRequest(SAVE_CURRENT_IDEA, id));
        if (id) {
            // idea already exists (whatever its state)
            return axios
                .put(`/api/ideas/${id}`, ideaData)
                .then(res => res.data)
                .then((data) => {
                    dispatch(updateCurrentIdea(data));
                    dispatch(createRequestSuccess(SAVE_CURRENT_IDEA, id));
                })
                .catch(() => dispatch(createRequestFailure(SAVE_CURRENT_IDEA, id)));
        }
        return axios
            .post('/api/ideas', ideaData)
            .then(res => res.data)
            .then((data) => {
                dispatch(setCurrentIdea(data));
                dispatch(createRequestSuccess(SAVE_CURRENT_IDEA, id));
            })
            .catch(() => dispatch(createRequestFailure(SAVE_CURRENT_IDEA, id)));
    };
}
