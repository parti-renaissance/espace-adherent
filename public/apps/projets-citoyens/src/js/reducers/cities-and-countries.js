import {
    CITIES_AND_COUNTIES,
    SET_COUNTRY,
} from '../actions/citizen-projects';

const DEFAULT_COUNTRIES = [{
    id: 'FR',
    value: 'France',
}, {
    id: 'US',
    value: 'United State of America',
}];

const DEFAULT_CITIES = {
    FR: ['Paris', 'Lyon'],
    US: ['New York', 'San Francisco'],
};

const INTITIAL_STATE = {
    allCities: {},
    countries: [],
    currentCountry: 'FR',
    cities: [],
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
        const { cities, countries } = action.payload;

        state = {
            ...state,
            allCities: Object.keys(cities).length ? cities : DEFAULT_CITIES,
            countries: countries.length ? countries : DEFAULT_COUNTRIES,
            loading: false,
            error: false,
        };
        state.cities = state.allCities[state.currentCountry];
        return state;
    case `${CITIES_AND_COUNTIES}_REJECTED`:
        return {
            ...state,
            error: true,
            loading: false,
        };

    case SET_COUNTRY:
        return {
            ...state,
            currentCountry: action.payload,
            cities: state.allCities[action.payload],
        };

    default:
        return state;
    }
}
