import axios from 'axios';

// Action types
export const REFERENT_DATA = 'FETCH_DATA';
export const COMMITTEE_FILTER = 'COMMITTEE_FILTER';
export const CONSOLE_LOG = 'CONSOLE_LOG';

// const REACT_APP_API_URL = process.env.REACT_APP_API_URL;
const REACT_APP_API_KEY = '2f363253ba81e173';
const getWorldData = '&cmd=getWorldData&data=population';
const REACT_APP_API_URL = `http://inqstatsapi.inqubu.com/?api_key=${REACT_APP_API_KEY}`;

export function callApi(filter = getWorldData) {
    console.log(filter);
    return dispatch =>
        axios.get(`${REACT_APP_API_URL}${filter}`).then((response) => {
            dispatch(fetchData(response.data));
        });
}

export function fetchData(value) {
    console.log(value);
    return {
        type: 'FETCH_DATA',
        value,
    };
}

export function committeeFilter(comitteeSelected) {
    return {
        type: 'COMMITTEE_FILTER',
        committeeFilter: comitteeSelected,
    };
}
