import { action } from '../helpers/actions';
import {
    SET_CURRENT_IDEA,
    UPDATE_CURRENT_IDEA,
    SET_GUIDELINES,
    TOGGLE_VOTE_CURRENT_IDEA,
} from '../constants/actionTypes';

export const setCurrentIdea = (data = {}) => action(SET_CURRENT_IDEA, { data });
export const updateCurrentIdea = data => action(UPDATE_CURRENT_IDEA, { data });
export const setGuidelines = data => action(SET_GUIDELINES, { data });
export const toggleVoteCurrentIdea = typeVote =>
    action(TOGGLE_VOTE_CURRENT_IDEA, { typeVote });
