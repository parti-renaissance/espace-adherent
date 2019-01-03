import { combineReducers } from 'redux';
import { connectRouter } from 'connected-react-router';
// TODO: uncomment when actually using it
// import ui from './ui';
import auth from './auth';
import loading from './loading';
import modal from './modal';
import ideas from './ideas';
import myIdeas from './myIdeas';
import currentIdea from './currentIdea';
import pinned from './pinned';
import reports from './reports';

const rootReducer = history =>
    combineReducers({
        router: connectRouter(history),
        // ui,
        auth,
        loading,
        modal,
        ideas,
        myIdeas,
        currentIdea,
        pinned,
        reports,
    });

export default rootReducer;
