import { store } from '../store';
import axios from 'axios';
import { FETCH_UNAUTHORIZED } from '../actions/auth';

// Add a response interceptor
axios.interceptors.response.use(
    r => r,
    (error) => {
        if (error.response && 401 === error.response.status) {
            store.dispatch({
                type: FETCH_UNAUTHORIZED,
                error: error.response,
            });
        }
        return Promise.reject(error);
    }
);

export default (endpoint, method = 'get', data) => {
    const headers =
		'get' === method
		    ? {}
		    : {
		        'Content-Type': 'application/json',
			  };

    return axios({
        url: `${process.env.REACT_APP_API_HOST}${endpoint}`,
        headers,
        method,
        withCredentials: true,
        data,
    }).then(r => r.data);
};
