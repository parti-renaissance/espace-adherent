import { combineReducers } from 'redux';
// TODO: uncomment when actually using it
// import ui from './ui';
import auth from './auth';
import loading from './loading';
import modal from './modal';
import ideas from './ideas';
import myIdeas from './myIdeas';
import myContributions from './myContributions';
import currentIdea from './currentIdea';
import pinned from './pinned';
import reports from './reports';
import staticData from './static';

const rootReducer = combineReducers({
    // ui,
    auth,
    loading,
    modal,
    ideas,
    myIdeas,
    myContributions,
    currentIdea,
    pinned,
    reports,
    static: staticData,
});

export default rootReducer;
