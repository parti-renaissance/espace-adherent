import { FETCH_AUTH_USER, SET_NICKNAME } from '../constants/actionTypes';
import { createRequest, createRequestSuccess, createRequestFailure } from '../actions/loading';
import { setAuthUser, updateAuthUser } from '../actions/auth';

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
                // update current user
                dispatch(updateAuthUser({ nickname, use_nickname: useNickname }));
                // set request success
                dispatch(createRequestSuccess(SET_NICKNAME));
            })
            .catch((error) => {
                const { response } = error;
                if (400 === response.status) {
                    // get error message from response format and add it to the failure payload
                    const violation = response.data.violations.find(v => 'nickname' === v.propertyPath);
                    dispatch(createRequestFailure(SET_NICKNAME, null, violation && violation.message));
                } else {
                    dispatch(createRequestFailure(SET_NICKNAME));
                }
            });
    };
}
