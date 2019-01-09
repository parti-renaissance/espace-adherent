import { action } from '../helpers/actions';
import {
    SET_CURRENT_IDEA,
    UPDATE_CURRENT_IDEA,
    UPDATE_CURRENT_IDEA_ANSWER,
    SET_GUIDELINES,
    TOGGLE_VOTE_CURRENT_IDEA,
} from '../constants/actionTypes';

export const setCurrentIdea = (data = {}) => action(SET_CURRENT_IDEA, { data });
export const updateCurrentIdea = data => action(UPDATE_CURRENT_IDEA, { data });
export const updateCurrentIdeaAnswer = (answerId, data) => action(UPDATE_CURRENT_IDEA_ANSWER, { answerId, data });
export const setGuidelines = data => action(SET_GUIDELINES, { data });
export const toggleVoteCurrentIdea = typeVote => action(TOGGLE_VOTE_CURRENT_IDEA, { typeVote });
