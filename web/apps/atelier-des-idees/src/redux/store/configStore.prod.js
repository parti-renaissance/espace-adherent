import { createStore, applyMiddleware } from 'redux';
import thunk from 'redux-thunk';
import axios from 'axios';
import rootReducer from '../reducers';

// axios config
const baseURL = 'undefined' === typeof window.config ? process.env.REACT_APP_EM_API_HOST : window.config.em_api_host;
const axiosInstance = axios.create({
    baseURL,
    headers: { Accept: 'application/json' },
});

const store = createStore(rootReducer, applyMiddleware(thunk.withExtraArgument(axiosInstance)));

export default store;
