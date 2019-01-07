import React from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import { selectLoadingState } from '../redux/selectors/loading';
import { selectIsAuthenticated } from '../redux/selectors/auth';

export default (ChildComponent) => {
    const withAuth = ({ isAuthLoading, isAuthenticated, ...props }) => {
        if (!isAuthLoading && !isAuthenticated) {
            window.location = '/connexion';
            return null;
        }
        return isAuthLoading ? null : <ChildComponent {...props} />;
    };

    withAuth.propTypes = {
        isAuthLoading: PropTypes.bool.isRequired,
        isAuthenticated: PropTypes.bool.isRequired,
    };

    function mapStateToProps(state) {
        const { isFetching } = selectLoadingState(state, 'FETCH_AUTH_USER');
        const isAuthenticated = selectIsAuthenticated(state);
        return {
            isAuthLoading: isFetching,
            isAuthenticated,
        };
    }

    return connect(
        mapStateToProps,
        {}
    )(withAuth);
};
