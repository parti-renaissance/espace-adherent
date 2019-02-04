import { action } from '../helpers/actions';
import { SET_AUTH_USER, UPDATE_AUTH_USER } from '../constants/actionTypes';

export const setAuthUser = user =>
    action(SET_AUTH_USER, {
        user,
    });

export const updateAuthUser = data => action(UPDATE_AUTH_USER, { data });
