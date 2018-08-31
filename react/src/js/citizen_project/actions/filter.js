import callApi from './../../utils/api';

// Action types
export const PROJECT_FILTER = 'PROJECT_FILTER';
export const FILTERED_ITEM = 'FILTERED_ITEM';
export const SET_COUNTRY = 'SET_COUNTRY';

const API = `${process.env.REACT_APP_API_URL}/citizen_projects`;

export function filterCitizenProjects({ keyword, category, city }) {
    const path = `?status=APPROVED&name=${keyword}&category=${category}&city=${city}`;
    return {
        type: PROJECT_FILTER,
        payload: callApi(API, path, {withCredentials: false, headers: {Accept: 'application/json'}}),
    };
}

export function setFilteredItem(value) {
    return {
        type: FILTERED_ITEM,
        payload: value,
    };
}

export function setCountry(value) {
    return {
        type: SET_COUNTRY,
        payload: value,
    };
}
