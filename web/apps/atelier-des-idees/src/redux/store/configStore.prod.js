import { createStore, applyMiddleware } from 'redux';
import thunk from 'redux-thunk';
import axios from 'axios';
import { createBrowserHistory } from 'history';
import { routerMiddleware } from 'connected-react-router';
import rootReducer from '../reducers';
import { initApp } from '../thunk/navigation';

// axios config
const baseURL = process.env.REACT_APP_ADI_BASE_URL || null;
const axiosInstance = axios.create({
    baseURL,
    headers: {
        Accept: 'application/json',
    },
});

// routing
export const history = createBrowserHistory();

const store = createStore(
    rootReducer(history),
    applyMiddleware(thunk.withExtraArgument(axiosInstance), routerMiddleware(history))
);

// initial dispatch
store.dispatch(initApp());

export default store;
