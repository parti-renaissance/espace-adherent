import { getAnswerThreads, getThread, getAnswerThreadsPagingData } from '../reducers/threads';

export const selectAnswerThreads = (state, answerId) => getAnswerThreads(state.threads, answerId);
export const selectThread = (state, id) => getThread(state.threads, id);
export const selectAnswerThreadsPagingData = (state, answerId) => getAnswerThreadsPagingData(state.threads, answerId);
