import {
  SET_AUTH_USER
} from '../constants/actionTypes'

const initialState = {
  isAuthenticated: false,
  user: null
}

function authReducer(state = initialState, action) {
  const {
    type,
    payload
  } = action
  switch (type) {
    case SET_AUTH_USER:
      return {
        isAuthenticated: true,
        user: payload.user
      }
    default:
      return state
  }
}

export default authReducer