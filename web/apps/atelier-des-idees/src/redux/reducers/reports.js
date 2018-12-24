import { ADD_REPORTS } from '../constants/actionTypes';

export const initialState = [];

const reportsReducer = (state = initialState, action) => {
    const { type, payload } = action;
    switch (type) {
    case ADD_REPORTS: {
        return [...state, ...payload.reports];
    }
    default:
        return state;
    }
};

export default reportsReducer;

export const getReports = state => state;
