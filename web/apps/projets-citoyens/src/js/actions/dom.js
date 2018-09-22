import callApi from '../utils/api';

const API = `${process.env.REACT_APP_EM_API}/dom`;

// Action types
export const GET_MARKUP = 'GET_MARKUP';

export function getServerMarkup() {
    return {
        type: GET_MARKUP,
        payload: callApi(API),
    };
}
