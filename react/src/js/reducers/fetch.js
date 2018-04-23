const defaultState = {
    committees: [],
};

export const fetch = (state = defaultState, action) => {
    if ('FETCH_DATA' === action.type) {
        return {
            ...state,
            committees: action.value,
        };
    }
    return {
        ...state,
    };
};
