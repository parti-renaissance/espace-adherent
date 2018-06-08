import thunk from 'redux-thunk';
import { createStore, applyMiddleware } from 'redux';
import { persistStore, persistReducer } from 'redux-persist';
import reducers from './reducers/index.js';
import { composeWithDevTools } from 'redux-devtools-extension';
import createHistory from 'history/createBrowserHistory';
import { routerMiddleware } from 'react-router-redux';
import promise from 'redux-promise-middleware';
import localForage from 'localforage';

const persistConfig = {
    key: 'root',
    storage: localForage,
};

export const history = createHistory();
const middleware = [promise(), thunk, routerMiddleware(history)];
export const store = createStore(
    persistReducer(persistConfig, reducers),
    composeWithDevTools(applyMiddleware(...middleware))
);

export const persistor = persistStore(store);
