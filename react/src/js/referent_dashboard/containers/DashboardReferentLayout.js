import React, { Component } from 'react';
import PropTypes from 'prop-types';

import { withRouter } from 'react-router';
import { connect } from 'react-redux';
import * as actionCreators from './../actions/user';

import Header from './../components/Header';
import DashboardPage from './DashboardPage';

class DashboardReferentLayout extends Component {
    componentDidMount() {
        this.props.getCurrentUser();
    }

    render() {
        const { user } = this.props;
        return (
            <div>
                <Header name={`${user.firstName} ${user.lastName}`} />
                <DashboardPage />
            </div>
        );
    }
}

const mapStateToProps = state => ({
    committees: state.stats.committees,
    user: state.user.user,
});

export default withRouter(
    connect(
        mapStateToProps,
        actionCreators
    )(DashboardReferentLayout)
);

DashboardReferentLayout.propTypes = {
    name: PropTypes.string,
};
