import { REFERENT_DATA, COMMITTEE_FILTER, CONSOLE_LOG } from './../actions';

const initiaState = {
    data: null,
    committee: null,
};

export default (state = initiaState, action) => {
    switch (action.type) {
    case REFERENT_DATA:
        return { ...state, data: action.data };
    case COMMITTEE_FILTER:
        return { ...state, committee: action.committee };
    case CONSOLE_LOG:
        console.log('This is good !');
    default:
        return state;
    }
};
