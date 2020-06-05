import {selectStatic} from './static';

export const selectRepublicanSilences = state => {
  const { republicanSilences } = selectStatic(state);
  return republicanSilences;
};
