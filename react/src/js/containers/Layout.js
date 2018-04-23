import React, { Component } from 'react';
import Nav from './../components/Nav';
import Header from './../components/Header';
import { connect } from 'react-redux';
import * as actionCreators from './../actions/index.js';
import { withRouter } from 'react-router';

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
