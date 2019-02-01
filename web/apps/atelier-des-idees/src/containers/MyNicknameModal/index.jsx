import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import MyNicknameModal from '../../components/Modal/MyNicknameModal';
import { setNickname } from '../../redux/thunk/auth';
import { selectLoadingState } from '../../redux/selectors/loading';
import { resetLoadingState } from '../../redux/actions/loading';

const SET_NICKNAME_REQ = 'SET_NICKNAME';

class MyNicknameModalContainer extends React.Component {
    componentWillUnmount() {
        this.props.onUnmount();
    }

    render() {
        return <MyNicknameModal {...this.props} />;
    }
}

MyNicknameModalContainer.propTypes = {
    onSubmit: PropTypes.func.isRequired,
    onUnmount: PropTypes.func.isRequired,
};

function mapStateToProps(state) {
    const submitState = selectLoadingState(state, SET_NICKNAME_REQ);
    return {
        isSubmitting: submitState.isFetching,
        isSubmitError: submitState.isError,
        isSubmitSuccess: submitState.isSuccess,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        onSubmit: (nickname, useNickname) => dispatch(setNickname(nickname, useNickname)),
        onUnmount: () => {
            dispatch(resetLoadingState(SET_NICKNAME_REQ));
        },
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(MyNicknameModalContainer);
