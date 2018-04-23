import thunk from 'redux-thunk';
import { createStore, applyMiddleware } from 'redux';
import reducers from './reducers/index.js';
import { composeWithDevTools } from 'redux-devtools-extension';
import createHistory from 'history/createBrowserHistory';
import { ConnectedRouter, routerReducer, routerMiddleware, push } from 'react-router-redux';

export const history = createHistory();
const middleware = [thunk, routerMiddleware(history)];
export const store = createStore(reducers, composeWithDevTools(applyMiddleware(...middleware)));
