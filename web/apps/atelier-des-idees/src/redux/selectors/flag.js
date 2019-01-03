import { getReasons, getFlag } from '../reducers/flag';

export const selectFlagReasons = state => getReasons(state.flag);
export const selectFlag = state => getFlag(state.flag);
