import React from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import { Redirect } from 'react-router-dom';
import { selectLoadingState } from '../redux/selectors/loading';
import { selectIsAuthenticated } from '../redux/selectors/auth';

export default ChildComponent => {
  const withAuth = ({ isAuthLoading, isAuthenticated, ...props }) => {
    if (!isAuthLoading && !isAuthenticated) {
      return <Redirect to="/connexion" />;
    }
    return isAuthLoading ? null : <ChildComponent {...props} />;
  };

  withAuth.propTypes = {
    isAuthLoading: PropTypes.bool.isRequired,
    isAuthenticated: PropTypes.bool.isRequired
  };

  function mapStateToProps(state) {
    const isAuthLoading = selectLoadingState(state, 'FETCH_AUTH_USER');
    const isAuthenticated = selectIsAuthenticated(state);
    return {
      isAuthLoading,
      isAuthenticated
    };
  }

  return connect(
    mapStateToProps,
    {}
  )(withAuth);
};
