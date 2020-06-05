import { routerReducer } from 'react-router-redux';
import { combineReducers } from 'redux';
import categories from './categories';
import locales from './cities-and-countries';
import citizen from './citizen-projects';
import turnkey from './turnkey-projects';

export default combineReducers({
    citizen,
    turnkey,
    locales,
    categories,
    routing: routerReducer,
});
