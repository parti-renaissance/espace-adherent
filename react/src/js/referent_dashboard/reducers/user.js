import { CURRENT_USER } from './../actions/user';

const defaultState = {
    user: {
        uuid: '',
        managedAreaTagCodes: [],
        country: '',
        zipCode: '',
        emailAddress: '',
        firstName: '',
        lastName: '',
    },
};

export default (state = defaultState, action) => {
    switch (action.type) {
    case `${CURRENT_USER}_FULFILLED`:
        return {
            ...state,
            user: action.payload,
        };
    default:
        return {
            ...state,
        };
    }
};
