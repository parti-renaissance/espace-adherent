import axios from "axios";

export default (endpoint, method = "get", data) => {
    const headers =
        method === "get"
            ? {}
            : {
                  "Content-Type": "application/json"
              };

    return axios({
        url: `${process.env.REACT_APP_API_URL}${endpoint}`,
        headers,
        method,
        withCredentials: true,
        data
    }).then(r => r.data);
};
