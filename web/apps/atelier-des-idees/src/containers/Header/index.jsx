import React from 'react';
import { connect } from 'react-redux';
import { showModal, hideModal } from '../../redux/actions/modal';
import { setNickname } from '../../redux/thunk/auth';
import { selectIsAuthenticated } from '../../redux/selectors/auth';
import Header from '../../components/Header';

import { MY_IDEAS_MODAL, MY_NICKNAME_MODAL } from '../../constants/modalTypes';

class HeaderContainer extends React.Component {
    componentWillUnmount() {
        this.props.unmountHeader();
    }

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
        unmountHeader: () => dispatch(hideModal()),
        onMyIdeasBtnClicked: tabActive => dispatch(showModal(MY_IDEAS_MODAL, { tabActive })),
        onMyNicknameClicked: () =>
            dispatch(
                showModal(MY_NICKNAME_MODAL, {
                    onSubmit: (nickname, useNickname) => dispatch(setNickname(nickname, useNickname)),
                })
            ),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(HeaderContainer);
