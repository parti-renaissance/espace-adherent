import {} from '../constants/actionTypes';

const initialState = [];

const ideasReducer = (state = initialState, action) => {
    const { type, payload } = action;
    switch (type) {
    default:
        return state;
    }
};

export default ideasReducer;
