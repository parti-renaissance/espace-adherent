import {
  getIsAuthenticated,
  getAuthUser
} from '../reducers/auth'

export const selectIsAuthenticated = state => getIsAuthenticated(state.auth)
export const selectAuthUser = state => getAuthUser(state.auth)
