import React, { Component } from 'react';
import PropTypes from 'prop-types';

import { withRouter } from 'react-router';
import { connect } from 'react-redux';
import * as actionCreators from './../actions/index.js';

import Nav from './../components/Nav';
import Header from './../components/Header';

class Layout extends Component {
    render() {
        const { children } = this.props;
        return (
            <div>
                <Header name="Mickaël-Ange" />
                <Nav />
                {children}
            </div>
        );
    }
}

const mapStateToProps = state => ({
    committees: state.fetch.committees,
});

export default withRouter(connect(mapStateToProps, actionCreators)(Layout));

Layout.propTypes = {
    name: PropTypes.string,
    children: PropTypes.element.isRequired,
};
