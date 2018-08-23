import { routerReducer } from 'react-router-redux';
import { combineReducers } from 'redux';
import filter from './filter';

export default combineReducers({
    filter,
    routing: routerReducer,
});
