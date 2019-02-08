import { action } from '../helpers/actions';
import { ADD_VISITED_IDEA } from '../constants/actionTypes';

export const addVisitedIdea = uuid => action(ADD_VISITED_IDEA, { uuid });
