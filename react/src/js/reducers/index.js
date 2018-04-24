import { combineReducers } from 'redux';
import { fetch } from './fetch';
import { filter } from './filter';
import { routerReducer } from 'react-router-redux';

export default combineReducers({
    fetch,
    filter,
    routing: routerReducer,
});
