import { FETCH_AUTH_USER } from '../constants/actionTypes';
import {
  createRequest,
  createRequestSuccess,
  createRequestFailure
} from '../actions/loading';
import { setAuthUser } from '../actions/auth';

export function fetchAuthUser() {
  return (dispatch, getState, axios) => {
    dispatch(createRequest(FETCH_AUTH_USER));
    return axios
      .get('/api/users/me')
      .then(res => res.data)
      .then(data => {
        // TODO: integration with actual data
        dispatch(setAuthUser(data));
        dispatch(createRequestSuccess(FETCH_AUTH_USER));
      })
      .catch(error => {
        // TODO: uncomment to force auth
        // dispatch(
        //   setAuthUser({
        //     name: 'Jean-Pierre F.'
        //   })
        // );
        dispatch(createRequestFailure(FETCH_AUTH_USER));
      });
  };
}
