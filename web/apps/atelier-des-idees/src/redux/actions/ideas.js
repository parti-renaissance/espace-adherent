import { action } from '../helpers/actions';
import {
    SET_IDEAS,
    ADD_IDEAS,
    REMOVE_IDEA,
    TOGGLE_VOTE_IDEA,
} from '../constants/actionTypes';

export const setIdeas = (items = [], metadata = {}) =>
    action(SET_IDEAS, { items, metadata });
export const addIdeas = (items = [], metadata = {}) =>
    action(ADD_IDEAS, { items, metadata });
export const removeIdea = id => action(REMOVE_IDEA, { id });
export const toggleVoteIdea = (id, typeVote) =>
    action(TOGGLE_VOTE_IDEA, { id, typeVote });
