import React from 'react';
import { connect } from 'react-redux';
import ReactModal from 'react-modal';
import { hideModal } from '../../redux/actions/modal';

const MODAL_COMPONENTS = {
    // ex:
    // modalTypes.TEST_MODAL: TestModal,
};

class ModalRoot extends React.Component {
    constructor(props) {
        super(props);
        this.closeModal = this.closeModal.bind(this);
    }

    closeModal() {
        this.props.hideModal();
    }

    render() {
        const { modalType, modalProps, isOpen } = this.props;
        if (!modalType) {
            return null;
        }

        const SpecificModal = MODAL_COMPONENTS[modalType];
        return (
            <ReactModal isOpen={isOpen} onRequestClose={this.closeModal} ariaHideApp={false}>
                <SpecificModal closeModal={this.closeModal} {...modalProps} />
            </ReactModal>
        );
    }
}

function mapStateToProps(state) {
    return { ...state.modal };
}

export default connect(
    mapStateToProps,
    { hideModal }
)(ModalRoot);
