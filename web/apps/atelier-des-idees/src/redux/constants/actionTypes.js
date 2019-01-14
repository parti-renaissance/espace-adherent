import { createRequestTypes } from '../helpers/actions';

// ui
export const SHOW_HEADER = 'SHOW_HEADER';
export const HIDE_HEADER = 'HIDE_HEADER';

// auth
export const SET_AUTH_USER = 'SET_AUTH_USER';
export const FETCH_AUTH_USER = createRequestTypes('FETCH_AUTH_USER');

// modal
export const SHOW_MODAL = 'SHOW_MODAL';
export const HIDE_MODAL = 'HIDE_MODAL';

// ideas
export const FETCH_IDEA = createRequestTypes('FETCH_IDEA');
export const FETCH_IDEAS = createRequestTypes('FETCH_IDEAS');
export const PUBLISH_IDEA = createRequestTypes('PUBLISH_IDEA');
export const SAVE_IDEA = createRequestTypes('SAVE_IDEA');
export const VOTE_IDEA = createRequestTypes('VOTE_IDEA');
export const SET_IDEAS = 'SET_IDEAS';
export const ADD_IDEAS = 'ADD_IDEAS';
export const REMOVE_IDEA = 'REMOVE_IDEA';
export const TOGGLE_VOTE_IDEA = 'TOGGLE_VOTE_IDEA';

// comments & threads
export const FETCH_IDEA_THREADS = createRequestTypes('FETCH_IDEA_THREADS');
export const POST_THREAD = createRequestTypes('POST_THREAD');
export const POST_THREAD_COMMENT = createRequestTypes('POST_THREAD_COMMENT');
export const SET_THREADS = 'SET_THREADS';
export const ADD_THREADS = 'ADD_THREADS';
export const REMOVE_THREAD = 'REMOVE_THREAD';
export const TOGGLE_APPROVE_THREAD = 'TOGGLE_APPROVE_THREAD';
export const SET_ANSWER_THREADS_PAGING = 'SET_ANSWER_THREADS_PAGING';
export const SET_THREAD_COMMENTS = 'SET_THREAD_COMMENTS';
export const ADD_THREAD_COMMENTS = 'ADD_THREAD_COMMENTS';
export const REMOVE_THREAD_COMMENT = 'REMOVE_THREAD_COMMENT';

// my ideas
export const FETCH_MY_IDEAS = createRequestTypes('FETCH_MY_IDEAS');
export const SET_MY_IDEAS = 'SET_MY_IDEAS';
export const REMOVE_MY_IDEA = 'REMOVE_MY_IDEA';

// my contributions
export const FETCH_MY_CONTRIBUTIONS = createRequestTypes('FETCH_MY_CONTRIBUTION');
export const SET_MY_CONTRIBUTIONS = 'SET_MY_CONTRIBUTIONS ';

// current idea
export const SAVE_CURRENT_IDEA = createRequestTypes('SAVE_CURRENT_IDEA');
export const PUBLISH_CURRENT_IDEA = createRequestTypes('PUBLISH_CURRENT_IDEA');
export const VOTE_CURRENT_IDEA = createRequestTypes('VOTE_CURRENT_IDEA');
export const SET_CURRENT_IDEA = 'SET_CURRENT_IDEA';
export const UPDATE_CURRENT_IDEA = 'UPDATE_CURRENT_IDEA';
export const UPDATE_CURRENT_IDEA_ANSWER = 'UPDATE_CURRENT_IDEA_ANSWER';
export const FETCH_GUIDELINES = createRequestTypes('FETCH_GUIDELINES');
export const SET_GUIDELINES = 'SET_GUIDELINES';
export const TOGGLE_VOTE_CURRENT_IDEA = 'TOGGLE_VOTE_CURRENT_IDEA';

// pinned
export const FETCH_CONSULTATION_PINNED = createRequestTypes('FETCH_CONSULTATION_PINNED');
export const SHOW_CONSULTATION_PINNED = 'SHOW_CONSULTATION_PINNED';
export const HIDE_CONSULTATION_PINNED = 'HIDE_CONSULTATION_PINNED';
// reports
export const FETCH_REPORTS = createRequestTypes('FETCH_REPORTS');
export const SET_REPORTS = 'SET_REPORTS';

// flag
export const ADD_FLAG = 'ADD_FLAG';

// static
export const UPDATE_STATIC = 'UPDATE_STATIC';
