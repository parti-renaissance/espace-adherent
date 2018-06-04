import thunk from 'redux-thunk';
import { createStore, applyMiddleware } from 'redux';
import reducers from './reducers/index.js';
import { composeWithDevTools } from 'redux-devtools-extension';
import createHistory from 'history/createBrowserHistory';
import { routerMiddleware } from 'react-router-redux';
import promise from 'redux-promise-middleware';

export const history = createHistory();
const middleware = [promise(), thunk, routerMiddleware(history)];
export const store = createStore(reducers, composeWithDevTools(applyMiddleware(...middleware)));
