import axios from 'axios';

// Action types
export const FETCH_DATA = 'FETCH_DATA';
export const COMMITTEE_FILTER = 'COMMITTEE_FILTER';
export const COMMITTEE_SEARCH = 'COMMITTEE_SEARCH';

// const REACT_APP_API_URL = process.env.REACT_APP_API_URL;
const REACT_APP_FAKE_API_KEY = '2f363253ba81e173';
const getWorldData = '&cmd=getWorldData&data=population';
const REACT_APP_FAKE_API_URL = `http://inqstatsapi.inqubu.com/?api_key=${REACT_APP_FAKE_API_KEY}`;

const REACT_APP_API_URL = 'https://staging.en-marche.fr/api/'; // adherents/count-by-referent-area

export function callApi(filter = getWorldData) {
    return dispatch =>
        axios.get(`${REACT_APP_FAKE_API_URL}${filter}`).then((response) => {
            dispatch(fetchData(response.data));
        });
}

export function fetchData(value) {
    // console.log(`Fetch data => ${value}`);
    return {
        type: FETCH_DATA,
        value,
    };
}

export function committeeFilter(value) {
    console.log(`Action Committee Filter => ${value}`);
    return {
        type: COMMITTEE_FILTER,
        value,
    };
}

export function committeeSearch(value) {
    console.log(`Action Committee Search => ${value}`);
    return {
        type: COMMITTEE_SEARCH,
        value,
    };
}
