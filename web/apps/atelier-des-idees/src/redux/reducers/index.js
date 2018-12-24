import { combineReducers } from 'redux';
import loading from './loading';
import modal from './modal';
import ideas from './ideas';
import pinned from './pinned';
import reports from './reports';

const rootReducer = combineReducers({ loading, modal, ideas, pinned, reports });

export default rootReducer;
