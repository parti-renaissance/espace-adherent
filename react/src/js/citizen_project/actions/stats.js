import callApi from './../../utils/api';

// Action types
export const MONTHLY_STATS = 'MONTHLY_STATS';
export const COMMITTEES_STATS = 'COMMITTEES_STATS';
export const ADHERENTS_STATS = 'ADHERENTS_STATS';
export const EVENTS_STATS = 'EVENTS_STATS';

export function getMonthlyStats(query) {
    if ('undefined' === typeof query) {
        query = '';
    }
    return {
        type: MONTHLY_STATS,
        payload: Promise.all([
            callApi(`/api/committees/members/count-by-month${query}`),
            callApi(`/api/events/count-by-month${query}`),
        ]).then(([committeesMembersMonthly, eventsMonthly]) => ({
            committeesMembersMonthly,
            eventsMonthly,
        })),
    };
}

export function getAdherentsStats(query) {
    return {
        type: ADHERENTS_STATS,
        payload: Promise.all([callApi('/api/adherents/count'), callApi('/api/adherents/count-by-referent-area')]).then(
            ([adherentsCount, adherents]) => ({
                adherentsCount,
                adherents,
            })
        ),
    };
}

export function getEventsStats(query) {
    return {
        type: EVENTS_STATS,
        payload: Promise.all([callApi('/api/events/count'), callApi('/api/events/count-participants')]).then(
            ([eventsCount, participantsCount]) => ({
                eventsCount,
                participantsCount,
            })
        ),
    };
}

export function getCommitteesStats(query) {
    return {
        type: COMMITTEES_STATS,
        payload: Promise.all([
            callApi('/api/committees/top-5-in-referent-area'),
            callApi('/api/committees/count-for-referent-area'),
        ]).then(([committeesTopFive, committees]) => ({
            committeesTopFive,
            committees,
        })),
    };
}
