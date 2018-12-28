import { createStore, applyMiddleware } from 'redux';
import thunk from 'redux-thunk';
import axios from 'axios';
import rootReducer from '../reducers';

// axios config
const axiosInstance = axios.create({
    headers: { Accept: 'application/json' },
});

const store = createStore(rootReducer, applyMiddleware(thunk.withExtraArgument(axiosInstance)));

export default store;
