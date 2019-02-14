import { getStatic } from '../reducers/static';

export const selectStatic = state => getStatic(state.static);
