import axios from 'axios';

// Action types
export const REFERENT_DATA = 'FETCH_DATA';
export const COMMITTEE_FILTER = 'COMMITTEE_FILTER';
export const CONSOLE_LOG = 'CONSOLE_LOG';

// const REACT_APP_API_URL = process.env.REACT_APP_API_URL;
const REACT_APP_API_KEY = '2f363253ba81e173';
const REACT_APP_API_URL = `http://inqstatsapi.inqubu.com/?api_key=${REACT_APP_API_KEY}&cmd=getWorldData&data=population`;

export function fetchData() {
    return dispatch =>
        axios.get(REACT_APP_API_URL).then((response) => {
            dispatch(changeColor(response.data));
        });
}

export function changeColor(value) {
    // console.log(value);
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
