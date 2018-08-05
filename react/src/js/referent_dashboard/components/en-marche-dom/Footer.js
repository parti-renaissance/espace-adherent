import React, { Component } from 'react';

import * as domFragmentActionCreators from './../../actions/domFragment';
import { connect } from 'react-redux';

class FooterFragment extends Component {
    componentDidMount() {
        this.props.getFooterFragment();
    }

    render() {
        return <p>Footer</p>;
    }
}

const mapStateToProps = state => ({
    footerFragment: state.domFragment.footerFragment,
});

export default connect(
    mapStateToProps,
    domFragmentActionCreators
)(FooterFragment);
