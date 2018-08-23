import callApi from '../../utils/api';

export const CITIZEN_PROJECTS = 'CITIZEN_PROJECTS';

const API = process.env.REACT_APP_CITIZEN_API;

export function getCitizenProjects() {
    return {
        type: CITIZEN_PROJECTS,
        payload: callApi(API, '?status=APPROVED'),
    };
}
