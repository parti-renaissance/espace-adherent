import { combineReducers } from 'redux';
import { connectRouter } from 'connected-react-router';
import ui from './ui';
import auth from './auth';
import loading from './loading';
import modal from './modal';
import ideas from './ideas';
import currentIdea from './currentIdea';
import pinned from './pinned';
import reports from './reports';

const rootReducer = history =>
    combineReducers({
        router: connectRouter(history),
        ui,
        auth,
        loading,
        modal,
        ideas,
        currentIdea,
        pinned,
        reports,
    });

export default rootReducer;
