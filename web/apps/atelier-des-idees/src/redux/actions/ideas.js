import { action } from '../helpers/actions';
import {
    SET_IDEAS,
    ADD_IDEAS,
    ADD_IDEAS_FINALIZED,
    ADD_IDEAS_PENDING,
    REMOVE_IDEA,
    TOGGLE_VOTE_IDEA,
} from '../constants/actionTypes';

export const setIdeas = (items = [], metadata = {}) => action(SET_IDEAS, { items, metadata });
export const addIdeas = (items = [], metadata = {}, namespace) => action(ADD_IDEAS, { items, metadata, namespace });

export const addIdeasFinalized = (items, metadata) => action(ADD_IDEAS_FINALIZED, { items, metadata });
export const addIdeasPending = (items, metadata) => action(ADD_IDEAS_PENDING, { items, metadata });
export const removeIdea = id => action(REMOVE_IDEA, { id });
export const toggleVoteIdea = (id, vote) => action(TOGGLE_VOTE_IDEA, { id, ...vote });
