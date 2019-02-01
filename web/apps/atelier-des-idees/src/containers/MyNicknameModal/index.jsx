import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import MyNicknameModal from '../../components/Modal/MyNicknameModal';
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
    return {};
}

export default connect(
    mapStateToProps,
    null
)(MyNicknameModal);
