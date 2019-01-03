import React from 'react';
import { connect } from 'react-redux';
import { showModal } from '../../redux/actions/modal';
import { fetchUserIdeas, fetchUserContributions } from '../../redux/thunk/ideas';
import Header from '../../components/Header';

import { MY_IDEAS_MODAL } from '../../constants/modalTypes';

class HeaderContainer extends React.Component {
    componentDidMount() {
        this.props.initHeader();
    }

    render() {
        const { initHeader, ...otherProps } = this.props;
        return <Header {...otherProps} />;
    }
}

function mapDispatchToProps(dispatch) {
    return {
        initHeader: () => {
            // user ideas
            dispatch(fetchUserIdeas());
            // user contributions
            dispatch(fetchUserContributions());
        },
        onMyIdeasBtnClicked: tabActive => dispatch(showModal(MY_IDEAS_MODAL, { tabActive })),
    };
}

export default connect(
    null,
    mapDispatchToProps
)(HeaderContainer);
