import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { withRouter } from 'react-router';
import { connect } from 'react-redux';
import * as actionCreators from './../actions/index.js';

import AdherentContainer from './AdherentContainer';
import CommitteeContainer from './CommitteeContainer';
import EventContainer from './EventContainer';
import data, { fakeNb } from './../fakeData/data';

class DashboardPage extends Component {
    constructor(props) {
        super(props);
        this.state = {
            fakeNb1: fakeNb(),
            fakeNb2: fakeNb(),
            fakeNb3: fakeNb(),
        };
    }
    componentDidMount() {
        this.props.callApi();
        console.log('Dashboard est monté');
    }

    componentWillUnmount() {
        console.log('Dashboard est démonté');
    }

    render() {
        {
            console.log('Dashboard rend');
        }
        const { committeeSelected, committees } = this.props;
        return (
            <div className="dashboard__ctn">
                <div className="wrapper">
                    <AdherentContainer
                        committees={committees}
                        committeeSelected={committeeSelected}
                        summaryTotal={this.state.fakeNb1}
                    />
                    <CommitteeContainer
                        committees={committees}
                        committeeSelected={committeeSelected}
                        summaryTotal={this.state.fakeNb2}
                    />
                    <EventContainer committees={committees} summaryTotal={this.state.fakeNb3} />
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
