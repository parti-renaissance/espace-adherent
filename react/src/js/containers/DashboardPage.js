import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { withRouter } from 'react-router';
import { connect } from 'react-redux';
import * as actionCreators from './../actions/index.js';

import AdherentContainer from './AdherentContainer';
import CommitteeContainer from './CommitteeContainer';
import EventContainer from './EventContainer';

class DashboardPage extends Component {
    componentDidMount() {
        this.props.callApi();
    }
    componentWillUnmount() {
        console.log('il est démonté');
    }
    render() {
        return (
            <div className="dashboard__ctn">
                <div className="wrapper">
                    <AdherentContainer
                        committees={this.props.committees}
                        committeeSelected={this.props.committeeSelected}
                    />
                    <CommitteeContainer
                        committees={this.props.committees}
                        committeeSelected={this.props.committeeSelected}
                    />
                    <EventContainer committees={this.props.committees} />
                </div>
            </div>
        );
    }
}

const mapStateToProps = state => ({
    committees: state.fetch.committees,
    committeeSelected: state.filter.committeeFilter,
});

export default withRouter(connect(mapStateToProps, actionCreators)(DashboardPage));

DashboardPage.propTypes = {
    committees: PropTypes.array,
};
