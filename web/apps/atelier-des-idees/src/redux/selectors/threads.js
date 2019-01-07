import { getAnswerThreads } from '../reducers/threads';

export const selectAnswerThreads = (state, answerId) => getAnswerThreads(state.threads, answerId);
