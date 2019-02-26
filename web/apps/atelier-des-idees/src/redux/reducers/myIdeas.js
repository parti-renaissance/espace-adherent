import { SET_MY_IDEAS, REMOVE_MY_IDEA } from '../constants/actionTypes';
import { ideaStatus } from '../../constants/api';

const { FINALIZED, DRAFT, PENDING } = ideaStatus;

export const initialState = {
    [DRAFT]: {
        items: [],
        metadata: {},
    },
    [PENDING]: {
        items: [],
        metadata: {},
    },
    [FINALIZED]: {
        items: [],
        metadata: {},
    },
};

const ideasReducer = (state = initialState, action) => {
    const { type, payload } = action;
    switch (type) {
    case SET_MY_IDEAS: {
        const { items, metadata, namespace } = payload;
        return {
            ...state,
            [namespace]: { items, metadata },
        };
    }
    case REMOVE_MY_IDEA: {
        return { ...state, items: state.items && state.items.filter(idea => idea.uuid !== payload.id) };
    }
    default:
        return state;
    }
};

export default ideasReducer;
