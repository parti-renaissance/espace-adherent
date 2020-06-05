import { ADD_VISITED_IDEA } from '../constants/actionTypes';

const intialState = { visitedIdeas: [] };

function sessionReducer(state = intialState, action) {
    const { type, payload } = action;
    switch (type) {
    case ADD_VISITED_IDEA: {
        const updatedVisitedIdeas = [...state.visitedIdeas];
        // add uuid only if never visited before
        if (!updatedVisitedIdeas.includes(payload.uuid)) {
            updatedVisitedIdeas.push(payload.uuid);
        }
        return { ...state, visitedIdeas: updatedVisitedIdeas };
    }
    default:
        return state;
    }
}

export default sessionReducer;

export const getVisitedIdeas = state => state.visitedIdeas;
