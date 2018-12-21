import { combineReducers } from 'redux';
import loading from './loading';
import modal from './modal';
import ideas from './ideas';

const rootReducer = combineReducers({ loading, modal, ideas });

export default rootReducer;
