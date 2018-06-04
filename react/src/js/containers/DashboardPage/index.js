import React, { Component } from 'react';
import { withRouter } from 'react-router';
import { connect } from 'react-redux';
import * as statsActionCreators from './../../actions/stats';
import * as filterActionCreators from './../../actions/filter';
import {
    selectAutocomplete,
    selectAutocompletePending,
    selectAdherents,
    selectFilteredItem,
    selectAdherentsCount,
    selectEventsMonthly,
    selectParticipantsCount,
    selectEventsCount,
    selectCommitteesTopFive,
    selectCommittees,
    selectGraphAdherentData,
    selectGraphMonthlyData,
    selectGraphEventsData,
} from './selectors';

import AdherentContainer from './AdherentContainer';
import CommitteeContainer from './CommitteeContainer';
import EventContainer from './EventContainer';

class DashboardPage extends Component {
    componentDidMount() {
        this.props.getCommitteesStats();
        this.props.getAdherentsStats();
        this.props.getEventsStats();
        this.props.getMonthlyStats();
    }

    render() {
        const {
            adherents,
            adherentsCount,
            autocomplete,
            autocompletePending,
            setFilteredItem,
            autocompleteSearch,
            committeeFilter,
            committeesTopFive,
            committeeSearchResult,
            filteredItem,
            participantsCount,
            eventsCount,
            getMonthlyStats,
            graphAdherentData,
            graphMonthlyData,
            graphEventsData,
            committees,
        } = this.props;

        return (
            <div className="dashboard__ctn">
                <div className="wrapper">
                    <AdherentContainer
                        summaryTotal={{
                            area: adherents,
                            total: adherentsCount,
                        }}
                        graphData={graphAdherentData}
                    />
                    <CommitteeContainer
                        graphData={graphMonthlyData}
                        onSelect={getMonthlyStats}
                        setFilteredItem={setFilteredItem}
                        filteredItem={filteredItem}
                        autocompleteSearch={autocompleteSearch}
                        committeeFilter={committeeFilter}
                        autocomplete={autocomplete}
                        autocompletePending={autocompletePending}
                        committeesFive={committeesTopFive}
                        committeeSearchResult={committeeSearchResult}
                        summaryTotal={committees}
                    />
                    <EventContainer
                        graphData={graphEventsData}
                        summaryTotal={{
                            events: eventsCount.current_total,
                            subscribed: participantsCount.total,
                        }}
                    />
                </div>
            </div>
        );
    }
}

const mapStateToProps = state => ({
    filteredItem: selectFilteredItem(state),
    autocomplete: selectAutocomplete(state),
    autocompletePending: selectAutocompletePending(state),
    adherents: selectAdherents(state),
    adherentsCount: selectAdherentsCount(state),
    eventsMonthly: selectEventsMonthly(state),
    eventsCount: selectEventsCount(state),
    participantsCount: selectParticipantsCount(state),
    committeesTopFive: selectCommitteesTopFive(state),
    committees: selectCommittees(state),
    graphAdherentData: selectGraphAdherentData(state),
    graphMonthlyData: selectGraphMonthlyData(state),
    graphEventsData: selectGraphEventsData(state),
});

export default withRouter(
    connect(mapStateToProps, {
        ...statsActionCreators,
        ...filterActionCreators,
    })(DashboardPage)
);

DashboardPage.propTypes = {};
