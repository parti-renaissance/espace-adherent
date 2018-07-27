import React, { Component } from 'react';

import * as domFragmentActionCreators from './../../actions/domFragment';
import { connect } from 'react-redux';

class HeaderFragment extends Component {
    componentDidMount() {
        this.props.getHeaderFragment();
    }

    render() {
        return <p>Header</p>;
    }
}

const mapStateToProps = state => ({
    // headerFragment: state.domFragment.headerFragment,
});

export default connect(
    mapStateToProps,
    domFragmentActionCreators
)(HeaderFragment);
