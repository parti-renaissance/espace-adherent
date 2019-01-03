import { FETCH_REPORTS } from '../constants/actionTypes';
import { setReports } from '../actions/reports';
import {
    createRequest,
    createRequestSuccess,
    createRequestFailure,
} from '../actions/loading';

const reportsMock = {
    metadata: {
        total_items: 2,
        items_per_page: 2,
        count: 2,
        current_page: 1,
        last_page: 1,
    },
    items: [
        {
            url:
				'https://storage.googleapis.com/en-marche-prod/documents/adherents/1-charte-et-manifeste/charte_des_valeurs.pdf',
            position: 1,
            name: 'Rapport sur les énergies renouvables',
        },
        {
            url:
				'https://storage.googleapis.com/en-marche-prod/documents/adherents/1-charte-et-manifeste/regles_de_fonctionnement_LaREM.pdf',
            position: 2,
            name: 'Rapport sur la politique du logement',
        },
    ],
};

export function fetchReports() {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_REPORTS));
        return (
            axios
        // TODO: Replace by real api
                .get('/api/consultation_reports')
                .then(res => res.data)
                .then(({ items, metadata }) => {
                    dispatch(setReports(items));
                    dispatch(createRequestSuccess(FETCH_REPORTS));
                })
                .catch((error) => {
                    dispatch(createRequestFailure(FETCH_REPORTS));
                })
        // TODO: remove finally when endpoint is up
                .finally(() => dispatch(setReports(reportsMock.items)))
        );
    };
}
