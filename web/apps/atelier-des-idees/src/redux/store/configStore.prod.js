import {
    createStore,
    applyMiddleware
} from 'redux';
import thunk from 'redux-thunk';
import axios from 'axios';
import rootReducer from '../reducers';
import {
    initApp
} from '../thunk/navigation'

// axios config
const baseURL = process.env.REACT_APP_ADI_BASE_URL || null;
const axiosInstance = axios.create({
    baseURL,
    headers: {
        Accept: 'application/json'
    },
});

const store = createStore(rootReducer, applyMiddleware(thunk.withExtraArgument(axiosInstance)));
// initial dispatch
store.dispatch(initApp())

export default store;
