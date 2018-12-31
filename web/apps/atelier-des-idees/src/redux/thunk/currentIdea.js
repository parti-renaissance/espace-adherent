import { ideaStatus } from '../../constants/api';
import { selectCurrentIdea } from '../selectors/currentIdea';
import { push } from 'connected-react-router';

/**
 * Delete an idea
 * @param {string} id idea to delete
 */
export function deleteCurrentIdea() {
    return (dispatch, getState, axios) => {
        const { id } = selectCurrentIdea(getState());
        if (id) {
            // idea already exists (whatever its state)
            return axios.delete(`/api/ideas/${id}`).then(() => dispatch(push('/atelier-des-idees')));
        }
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
