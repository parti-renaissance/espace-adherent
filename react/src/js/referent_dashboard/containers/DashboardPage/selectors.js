import { createSelector } from 'reselect';
import _ from 'lodash';

export const selectFilteredItem = state => state.filter.filteredItem;
export const selectAutocomplete = state => state.filter.autocomplete;
export const selectAutocompletePending = state => state.filter.autocompletePending;
export const selectAdherents = state => state.stats.adherents;
export const selectAdherentsCount = state => state.stats.adherentsCount;
export const selectEventsMonthly = state => state.stats.eventsMonthly;
export const selectParticipantsCount = state => state.stats.participantsCount;
export const selectEventsCount = state => state.stats.eventsCount;
export const selectCommitteesMembersMonthly = state => state.stats.committeesMembersMonthly;
export const selectCommitteesTopFive = state => state.stats.committeesTopFive;
export const selectCommittees = state => state.stats.committees;

const renameProperty = (objects, oldName, newName) => {
    if (_.isNil(objects) || oldName === newName) {
        return objects;
    }
    for (const object of objects) {
        if (object.hasOwnProperty(oldName)) {
            object[newName] = object[oldName];
            delete object[oldName];
        }
    }
    return objects;
};

export const selectGraphAdherentData = createSelector(selectAdherents, adherents =>
    _(adherents.adherents)
        .keyBy('date')
        .merge(_.keyBy(adherents.committee_members, 'date'))
        .merge(_.keyBy(adherents.email_subscriptions, 'date'))
        .values()
        .value()
        .reverse()
);

export const selectGraphMonthlyData = createSelector(
    selectCommitteesMembersMonthly,
    selectEventsMonthly,
    (committeesMembersMonthly, eventsMonthly) =>
        _(committeesMembersMonthly.committee_members)
            .keyBy('date')
            .merge(_.keyBy(eventsMonthly.event_participants, 'date'))
            .merge(_.keyBy(eventsMonthly.events, 'date'))
            .values()
            .value()
            .reverse()
);

export const selectGraphEventsData = createSelector(
    selectParticipantsCount,
    selectEventsCount,
    (participantsCount, eventsCount) =>
        _(eventsCount.events)
            .keyBy('date')
            .merge(_.keyBy(renameProperty(participantsCount.participants, 'count', 'participants'), 'date'))
            .merge(
                _.keyBy(
                    renameProperty(participantsCount.participants_as_adherent, 'count', 'adherentsParticipants'),
                    'date'
                )
            )
            .merge(_.keyBy(renameProperty(eventsCount.referent_events, 'count', 'referent'), 'date'))
            .values()
            .value()
            .reverse()
);
