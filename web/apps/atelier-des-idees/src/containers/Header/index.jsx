import React from 'react';
import { connect } from 'react-redux';
import { showModal } from '../../redux/actions/modal';
import { selectIsAuthenticated } from '../../redux/selectors/auth';
import Header from '../../components/Header';

import { MY_IDEAS_MODAL } from '../../constants/modalTypes';

class HeaderContainer extends React.Component {
    render() {
        const { initHeader, ...otherProps } = this.props;
        return <Header {...otherProps} />;
    }
}

function mapStateToProps(state) {
    const isAuthenticated = selectIsAuthenticated(state);
    return { isAuthenticated };
}

function mapDispatchToProps(dispatch) {
    return {
        onMyIdeasBtnClicked: tabActive => dispatch(showModal(MY_IDEAS_MODAL, { tabActive })),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(HeaderContainer);
