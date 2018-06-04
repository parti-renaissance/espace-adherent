import { MONTHLY_STATS, COMMITTEES_STATS, EVENTS_STATS, ADHERENTS_STATS } from '../actions/stats';

const defaultState = {
    adherentsCount: {
        female: 0,
        male: 0,
        total: 0,
    },
    adherents: {
        female: 0,
        male: 0,
        total: 0,
        adherents: [],
        committee_members: [],
        email_subscriptions: [],
    },
    eventsMonthly: {
        events: [],
        event_participants: [],
    },
    participantsCount: {
        total: 0,
        participants: [],
        participants_as_adherent: [],
    },
    eventsCount: {
        current_total: 0,
        events: [],
        referent_events: [],
    },
    committeesMembersMonthly: {
        committee_members: [],
    },
    committeesTopFive: {
        most_active: [],
        least_active: [],
    },
    committees: {
        committees: 0,
        members: {
            female: 0,
            male: 0,
            total: 0,
        },
        supervisors: {
            female: 0,
            male: 0,
            total: 0,
        },
    },
};

export default (state = defaultState, action) => {
    switch (action.type) {
    case `${MONTHLY_STATS}_FULFILLED`:
        const { eventsMonthly, committeesMembersMonthly } = action.payload;
        return {
            ...state,
            committeesMembersMonthly,
            eventsMonthly,
        };
    case `${ADHERENTS_STATS}_FULFILLED`:
        const { adherentsCount, adherents } = action.payload;
        return {
            ...state,
            adherentsCount,
            adherents,
        };
    case `${EVENTS_STATS}_FULFILLED`:
        const { eventsCount, participantsCount } = action.payload;
        return {
            ...state,
            eventsCount,
            participantsCount,
        };
    case `${COMMITTEES_STATS}_FULFILLED`:
        const { committeesTopFive, committees } = action.payload;
        return {
            ...state,
            committeesTopFive,
            committees,
        };
    default:
        return state;
    }
};
