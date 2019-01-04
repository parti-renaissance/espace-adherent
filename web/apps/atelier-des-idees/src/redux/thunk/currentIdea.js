import { ideaStatus } from '../../constants/api';
import history from '../../history';
import { SAVE_CURRENT_IDEA, PUBLISH_CURRENT_IDEA, FETCH_GUIDELINES } from '../constants/actionTypes';
import { publishIdea, saveIdea } from '../thunk/ideas';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { selectCurrentIdea } from '../selectors/currentIdea';
import { setCurrentIdea, updateCurrentIdea, setGuidelines } from '../actions/currentIdea';
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
                history.push('/atelier-des-idees');
            });
        }
        dispatch(hideModal());
        history.push('/atelier-des-idees');
    };
}

export function goBackFromCurrentIdea() {
    return (dispatch, getState) => {
        const { status } = selectCurrentIdea(getState());
        switch (status) {
        case ideaStatus.FINALIZED:
            history.push('/atelier-des-idees/consulter');
            break;
        case ideaStatus.PENDING:
            history.push('/atelier-des-idees/contribuer');
            break;
        case ideaStatus.DRAFT:
        default:
            history.push('/atelier-des-idees/proposer');
        }
    };
}

export function saveCurrentIdea(ideaData) {
    return (dispatch, getState, axios) => {
        const { uuid } = selectCurrentIdea(getState());
        dispatch(createRequest(SAVE_CURRENT_IDEA, uuid));
        if (uuid) {
            // idea already exists (whatever its state)
            return axios
                .put(`/api/ideas/${uuid}`, ideaData)
                .then(res => res.data)
                .then((data) => {
                    dispatch(updateCurrentIdea(data));
                    dispatch(createRequestSuccess(SAVE_CURRENT_IDEA, uuid));
                })
                .catch(() => dispatch(createRequestFailure(SAVE_CURRENT_IDEA, uuid)));
        }
        return axios
            .post('/api/ideas', ideaData)
            .then(res => res.data)
            .then((data) => {
                dispatch(setCurrentIdea(data));
                dispatch(createRequestSuccess(SAVE_CURRENT_IDEA));
                // TODO: uncomment when page exists
                // replace location with `/atelier-des-idees/note/${data.uuid}`
            })
            .catch(() => dispatch(createRequestFailure(SAVE_CURRENT_IDEA)));
    };
}

export function publishCurrentIdea(ideaData) {
    return (dispatch, getState, axios) => {
        const { id } = selectCurrentIdea(getState());
        dispatch(createRequest(PUBLISH_CURRENT_IDEA));
        dispatch(saveIdea(id, ideaData))
            .then((data) => {
                const uuid = id || data.uuid;
                dispatch(publishIdea(uuid)).then(() => dispatch(createRequestSuccess(PUBLISH_CURRENT_IDEA)));
            })
            .catch(() => dispatch(createRequestFailure(PUBLISH_CURRENT_IDEA)));
    };
}

export function fetchGuidelines() {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_GUIDELINES));
        axios
            .get('/api/guidelines')
            .then(res => res.data)
            .then((data) => {
                dispatch(setGuidelines(data));
                dispatch(createRequestSuccess(FETCH_GUIDELINES));
            })
            .catch(() => dispatch(createRequestFailure(FETCH_GUIDELINES)));
    };
}
