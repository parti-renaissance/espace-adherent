import { combineReducers } from 'redux';
import { fetch } from './fetch';
import { filter } from './filter';
import { ConnectedRouter, routerReducer, routerMiddleware, push } from 'react-router-redux';

export default combineReducers({
    fetch,
    filter,
    routing: routerReducer,
});
