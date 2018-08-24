import axios from 'axios';

const DEFAULT_OPS = {
    method: 'get',
    withCredentials: true,
    headers: {},
    maxRedirects: 0,
};

// Add a response interceptor
axios.interceptors.response.use(
    r => r,
    (error) => {
        if (error.response && 401 === error.response.status) {
            window.location = `${process.env.REACT_APP_API_URL}/connexion`;
        }
        return Promise.reject(error);
    }
);

export default (host, endpoint = '', options) => {
    options = {
        url: `${host}${endpoint}`,
        ...DEFAULT_OPS,
        ...options,
    };
    if ('get' !== options.method) {
        options.headers['Content-Type'] = 'application/json';
    }
    return axios(options).then(r => r.data);
};
