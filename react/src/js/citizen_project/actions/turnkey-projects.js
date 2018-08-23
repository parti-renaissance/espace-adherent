import callApi from '../../utils/api';

export const PINNED = 'PINNED';
export const TURNKEY_PROJECTS = 'TURNKEY_PROJECTS';
export const TURNKEY_DETAIL = 'TURNKEY_DETAIL';

const API = process.env.REACT_APP_TURNKEY_API;

export function getPinned() {
    return {
        type: PINNED,
        payload: callApi(API, '/pinned'),
    };
}

export function getTurnkeyProjects() {
    return {
        type: TURNKEY_PROJECTS,
        payload: callApi(API, '/'),
    };
}

export function getTurnkeyDetail(slug) {
    return {
        type: TURNKEY_DETAIL,
        payload: callApi(API, `/${slug}`),
    };
}
