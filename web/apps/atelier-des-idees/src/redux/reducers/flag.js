import { SET_FLAG_REASONS, POST_FLAG } from '../constants/actionTypes';

export const initialState = { reasons: {}, flag: { reasons: [], comment: '' } };

const flagReducer = (state = initialState, action) => {
    const { type, payload } = action;
    switch (type) {
    case SET_FLAG_REASONS: {
        return payload.reasons;
    }
    case POST_FLAG: {
        const { data } = payload;
        return {
            ...state.reasons,
            flag: { reasons: [...data.reasons], comment: data.comment },
        };
    }
    default:
        return state;
    }
};

export default flagReducer;

export const getReasons = state => state.reasons;
export const getFlag = state => state.flag;
