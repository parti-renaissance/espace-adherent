import { combineReducers } from 'redux';
import loading from './loading';
import modal from './modal';
import ideas from './ideas';
import pinned from './pinned';

const rootReducer = combineReducers({ loading, modal, ideas, pinned });

export default rootReducer;
