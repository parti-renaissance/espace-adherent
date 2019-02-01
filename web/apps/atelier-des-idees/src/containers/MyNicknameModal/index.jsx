import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import MyNicknameModal from '../../components/Modal/MyNicknameModal';
import { setNickname } from '../../redux/thunk/auth';
import { selectLoadingState } from '../../redux/selectors/loading';

class MyNicknameModalContainer extends React.Component {
    render() {
        return <MyNicknameModal {...this.props} />;
    }
}

MyNicknameModalContainer.propTypes = {
    onSubmit: PropTypes.func.isRequired,
};

function mapStateToProps(state) {
    const submitState = selectLoadingState(state, 'SET_NICKNAME');
    return {
        isSubmitting: submitState.isFetching,
        isSubmitError: submitState.isError,
        isSubmitSuccess: submitState.isSuccess,
    };
}

export default connect(
    mapStateToProps,
    {
        onSubmit: setNickname,
    }
)(MyNicknameModal);
