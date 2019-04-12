import { action } from '../helpers/actions';
import {
    SET_CURRENT_IDEA,
    UPDATE_CURRENT_IDEA,
    UPDATE_CURRENT_IDEA_ANSWER,
    SET_GUIDELINES,
    TOGGLE_VOTE_CURRENT_IDEA,
    SET_AUTOCOMPLETE_RESULT,
    EXTEND_PERIOD,
} from '../constants/actionTypes';

export const setCurrentIdea = (data = {}) => action(SET_CURRENT_IDEA, { data });
export const autoCompleteTitleIdea = data => action(SET_AUTOCOMPLETE_RESULT, { data });
export const updateCurrentIdea = data => action(UPDATE_CURRENT_IDEA, { data });
export const extendPeriod = data => action(EXTEND_PERIOD, { data });
export const updateCurrentIdeaAnswer = (answerId, data) => action(UPDATE_CURRENT_IDEA_ANSWER, { answerId, data });
export const setGuidelines = data => action(SET_GUIDELINES, { data });
export const toggleVoteCurrentIdea = vote => action(TOGGLE_VOTE_CURRENT_IDEA, { ...vote });
