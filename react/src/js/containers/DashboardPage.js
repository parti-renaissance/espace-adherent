import React, { Component } from 'react';

import AdherentContainer from './AdherentContainer';
import CommitteeContainer from './CommitteeContainer';
import EventContainer from './EventContainer';
import { connect } from 'react-redux';
import * as actionCreators from './../actions/index.js';
import { withRouter } from 'react-router';

class DashboardPage extends Component {
    componentDidMount() {
        console.log('il est monté');
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
