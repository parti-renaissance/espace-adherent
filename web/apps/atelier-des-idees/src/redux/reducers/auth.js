import { SET_AUTH_USER } from '../constants/actionTypes';

// TODO: uncomment when linked to users api
// const initialState = {
//     isAuthenticated: false,
//     user: {},
// };

// TODO: remove when linked to users api
const initialState = {
    isAuthenticated: true,
    user: { uuid: '0000', name: 'Jean-Pierre F.' },
};

function authReducer(state = initialState, action) {
    const { type, payload } = action;
    switch (type) {
    case SET_AUTH_USER:
        return {
            isAuthenticated: true,
            user: payload.user,
        };
    default:
        return state;
    }
}

export default authReducer;

export const getIsAuthenticated = state => state.isAuthenticated;
export const getAuthUser = state => state.user;
