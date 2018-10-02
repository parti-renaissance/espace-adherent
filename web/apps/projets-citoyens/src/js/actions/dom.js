import callApi from '../utils/api';

// Action types
export const GET_MARKUP = 'GET_MARKUP';

export function getServerMarkup() {
    return {
        type: GET_MARKUP,
        payload: callApi('/dom'),
    };
}
