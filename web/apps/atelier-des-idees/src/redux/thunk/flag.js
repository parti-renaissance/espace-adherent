import { FETCH_FLAG_REASONS } from '../constants/actionTypes';
import {
    createRequest,
    createRequestSuccess,
    createRequestFailure,
} from '../actions/loading';
import { setFlagReasons, postFlag } from '../actions/flag';
import { selectFlag } from '../selectors/flag';

const reasonsMock = {
    en_marche_values: 'Ce que je vois ne correspond pas aux valeurs du Mouvement',
    inappropriate: 'Ce n\'est pas du contenu approprié',
    commercial_content: 'Il s\'agit de contenu commercial',
    other: 'Autre',
};

export function fetchFlagReasons() {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_FLAG_REASONS));
        return (
            axios
                .get('/api/report/reasons')
                .then(res => res.data)
                .then((data) => {
                    dispatch(setFlagReasons(data));
                    dispatch(createRequestSuccess(FETCH_FLAG_REASONS));
                })
                .catch((error) => {
                    dispatch(createRequestFailure(FETCH_FLAG_REASONS));
                })
        // TODO: remove finally when endpoint is up
                .finally(() => dispatch(setFlagReasons(reasonsMock)))
        );
    };
}
