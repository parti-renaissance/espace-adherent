import axios from 'axios';

// Action types
export const REFERENT_DATA = 'FETCH_DATA';
export const COMMITTEE_FILTER = 'COMMITTEE_FILTER';

// Action creators

export function fetchData(data) {
    // Function qui fait le call avec axios
    return { type: REFERENT_DATA, data };
}

export function filterCommittee(committee) {
    return { type: COMMITTEE_FILTER, committee };
}
