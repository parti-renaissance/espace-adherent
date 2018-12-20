import { combineReducers } from 'redux';
import modal from './modal';
import ideas from './ideas';

const rootReducer = combineReducers({ modal, ideas });

export default rootReducer;
