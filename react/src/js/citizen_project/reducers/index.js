import { combineReducers } from 'redux';
import filter from './filter';
import { routerReducer } from 'react-router-redux';

export default combineReducers({
    filter,
    routing: routerReducer,
});
