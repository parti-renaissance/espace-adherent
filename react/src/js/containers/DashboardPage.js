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
        this.props.fetchData();
    }
    componentWillUnmount() {
        console.log('il est démonté');
    }
    render() {
        return (
            <div className="dashboard__ctn">
                <div className="wrapper">
                    <AdherentContainer committees={this.props.committees} />
                    <CommitteeContainer committees={this.props.committees} />
                    <EventContainer committees={this.props.committees} />
                </div>
            </div>
        );
    }
}

const mapStateToProps = state => ({
    committees: state.fetch.committees,
});

export default withRouter(connect(mapStateToProps, actionCreators)(DashboardPage));

DashboardPage.propTypes = {
    committees: PropTypes.array,
};
