import React, { Component } from 'react';

import { withRouter } from 'react-router';
import * as domFragmentActionCreators from './../../actions/domFragment';
import { connect } from 'react-redux';

class HeaderFragment extends Component {
    componentDidMount() {
        this.props.getHeaderFragment();
    }

    render() {
        const { headerFragment } = this.props;
        return (
            <div>
                {' '}
                {headerFragment}
                {console.log(headerFragment)}
            </div>
        );
    }
}

const mapStateToProps = state => ({
    headerFragment: state.domFragment.headerFragment,
});

export default withRouter(
    connect(
        mapStateToProps,
        domFragmentActionCreators
    )(HeaderFragment)
);
