import callApi from '../../utils/api';

export const CITIZEN_PROJECTS = 'CITIZEN_PROJECTS';
export const CATEGORIES = 'CATEGORIES';
export const CITIES_AND_COUNTIES = 'CITIES_AND_COUNTIES';
export const FILTER_PROJECTS = 'FILTER_PROJECTS';

const API = process.env.REACT_APP_CITIZEN_API;

const options = {
    withCredentials: false,
    headers: {
        Accept: 'application/json',
    },
};

export function getCitizenProjects() {
    return {
        type: CITIZEN_PROJECTS,
        payload: callApi(API, '?status=APPROVED', options),
    };
}

export function getCategories() {
    return {
        type: CATEGORIES,
        payload: callApi(API, '/categories', options),
    };
}

export function getCitiesAndCountries() {
    return {
        type: CITIES_AND_COUNTIES,
        payload: callApi(API, '/localizations', options),
    };
}
