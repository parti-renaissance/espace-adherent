import { FETCH_AUTH_USER, SET_NICKNAME } from '../constants/actionTypes';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { setAuthUser } from '../actions/auth';

export function fetchAuthUser() {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(FETCH_AUTH_USER));
        return axios
            .get('/api/users/me')
            .then(res => res.data)
            .then((data) => {
                dispatch(setAuthUser(data));
                dispatch(createRequestSuccess(FETCH_AUTH_USER));
            })
            .catch((error) => {
                dispatch(createRequestFailure(FETCH_AUTH_USER));
            });
    };
}

export function setNickname(nickname, useNickname) {
    return (dispatch, getState, axios) => {
        dispatch(createRequest(SET_NICKNAME));
        return axios
            .put('/api/adherents/me/anonymize', { nickname, use_nickname: useNickname })
            .then(() => {
                dispatch(createRequestSuccess(SET_NICKNAME));
            })
            .catch(() => {
                dispatch(createRequestFailure(SET_NICKNAME));
            });
    };
}
