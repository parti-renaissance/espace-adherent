import { getAnswerThreads, getThread, getCommentsByThreadId, getAnswerThreadsPagingData } from '../reducers/threads';

export const selectAnswerThreads = (state, answerId) => getAnswerThreads(state.threads, answerId);
export const selectThread = (state, id) => getThread(state.threads, id);
export const selectAnswerThreadsPagingData = (state, answerId) => getAnswerThreadsPagingData(state.threads, answerId);
export const selectCommentsByThreadId = (state, threadId) => getCommentsByThreadId(state.threads, threadId);
export const selectAnswerThreadsWithReplies = (state, answerId) => {
    const threads = getAnswerThreads(state.threads, answerId);
    return threads.map(thread => ({
        ...thread,
        replies: selectCommentsByThreadId(state, thread.uuid),
        nbReplies: thread.comments.total_items,
    }));
};
