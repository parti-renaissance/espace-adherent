import { createStore, applyMiddleware, compose } from 'redux';
import thunk from 'redux-thunk';
import axios from 'axios';
import rootReducer from '../reducers';
import { initApp } from '../thunk/navigation';

// axios config
const baseURL = process.env.REACT_APP_ADI_BASE_URL || null;
const axiosInstance = axios.create({
    baseURL,
    withCredentials: true,
    headers: {
        Accept: 'application/json',
    },
});

const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
const store = createStore(rootReducer, composeEnhancers(applyMiddleware(thunk.withExtraArgument(axiosInstance))));

// initial dispatch
store.dispatch(initApp());

export default store;
