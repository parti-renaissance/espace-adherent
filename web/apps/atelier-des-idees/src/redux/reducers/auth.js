import { SET_AUTH_USER } from '../constants/actionTypes';

// const initialState = {
//     isAuthenticated: false,
//     user: {},
// };

// TODO: uncomment below and comment above to mock auth
const initialState = {
    isAuthenticated: true,
    user: { uuid: '0000', firstName: 'Jean-Pierre', lastName: 'Français' },
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
