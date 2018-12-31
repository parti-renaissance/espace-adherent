import { combineReducers } from 'redux';
import auth from './auth';
import loading from './loading';
import modal from './modal';
import ideas from './ideas';
import currentIdea from './currentIdea';
import pinned from './pinned';
import reports from './reports';

const rootReducer = combineReducers({
    auth,
    loading,
    modal,
    ideas,
    currentIdea,
    pinned,
    reports,
});

export default rootReducer;
