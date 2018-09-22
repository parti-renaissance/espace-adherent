import thunk from 'redux-thunk';
import { createStore, applyMiddleware } from 'redux';
import { persistStore, persistReducer } from 'redux-persist';
import { composeWithDevTools } from 'redux-devtools-extension';
import createHistory from 'history/createBrowserHistory';
import { routerMiddleware } from 'react-router-redux';
import promise from 'redux-promise-middleware';
import localForage from 'localforage';
import logger from 'redux-logger';

import projectReducers from './reducers/index.js';

const persistConfig = {
    key: 'root',
    storage: localForage,
};

export const history = createHistory();
const middleware = [promise(), thunk, routerMiddleware(history)];

if ('development' === process.env.NODE_ENV) {
    middleware.push(logger);
}

export const store = createStore(
    persistReducer(persistConfig, projectReducers),
    composeWithDevTools(applyMiddleware(...middleware))
);

export const persistor = persistStore(store);
