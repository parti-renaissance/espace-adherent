import {
    CITIES_AND_COUNTIES,
} from '../actions/citizen-projects';

const INTITIAL_STATE = {
    cities: [],
    countries: [],
    error: false,
    loading: false,
};

export default function citiesAndCountriesReducer(state = INTITIAL_STATE, action) {
    switch (action.type) {
    case `${CITIES_AND_COUNTIES}_PENDING`:
        return {
            ...state,
            loading: true,
            error: false,
        };
    case `${CITIES_AND_COUNTIES}_FULFILLED`:
        return {
            ...state,
            cities: action.payload.cities,
            countries: action.payload.countries,
            loading: false,
            error: false,
        };
    case `${CITIES_AND_COUNTIES}_REJECTED`:
        return {
            ...state,
            error: true,
            loading: false,
        };

    default:
        return state;
    }
}
