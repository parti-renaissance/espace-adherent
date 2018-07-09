import axios from 'axios';

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

export default (endpoint, method = 'get', data) => {
    const headers =
        'get' === method
            ? {}
            : {
                'Content-Type': 'application/json',
            };

    return axios({
        url: `${process.env.REACT_APP_API_URL}${endpoint}`,
        headers,
        method,
        withCredentials: true,
        maxRedirects: 0,
        data,
    }).then(r => r.data);
};
