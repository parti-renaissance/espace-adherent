const defaultState = {
    committeeFilter: '',
};

export const filter = (state = defaultState, action) => {
    if ('COMMITTEE_FILTER' === action.type) {
        return {
            ...state,
            committeeFilter: action.committeeFilter,
        };
    }
    return {
        ...state,
    };
};
