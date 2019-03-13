import React from 'react';
import { connect } from 'react-redux';
import ReactModal from 'react-modal';
import * as modalTypes from '../../constants/modalTypes';
import { hideModal } from '../../redux/actions/modal';
import { selectModalData } from '../../redux/selectors/modal';

// components
import ReportsModal from '../../components/Modal/ReportsModal';
import PublishIdeaFormModal from '../../containers/PublishIdeaFormModal';
import DeleteIdeaModal from '../../components/Modal/DeleteIdeaModal';
import MyNicknameModal from '../../containers/MyNicknameModal';
import MyIdeasContainer from '../../containers/MyIdeas';
import FlagModal from '../../containers/FlagModal';
import DeleteCommentModal from '../../components/Modal/DeleteCommentModal';
import icn_close from './../../img/icn_close.svg';

const MODAL_COMPONENTS = {
    // to use a modal, just add it below with its corresponding type
    // ex:
    // [modalTypes.TEST_MODAL]: TestModal,
    [modalTypes.REPORTS_MODAL]: ReportsModal,
    [modalTypes.PUBLISH_IDEA_MODAL]: PublishIdeaFormModal,
    [modalTypes.DELETE_IDEA_MODAL]: DeleteIdeaModal,
    [modalTypes.MY_IDEAS_MODAL]: MyIdeasContainer,
    [modalTypes.FLAG_MODAL]: FlagModal,
    [modalTypes.MY_NICKNAME_MODAL]: MyNicknameModal,
    [modalTypes.DELETE_COMMENT_MODAL]: DeleteCommentModal,
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
            <ReactModal
                closeTimeoutMS={200}
                className="modal"
                overlayClassName="modal-overlay"
                isOpen={isOpen}
                onRequestClose={this.closeModal}
                ariaHideApp={false}
            >
                <button className="modal-closeBtn" onClick={this.closeModal}>
                    <img src={icn_close} alt="Fermer" />
                </button>
                <div className="modal-content-wrapper">
                    <SpecificModal closeModal={this.closeModal} {...modalProps} />
                </div>
            </ReactModal>
        );
    }
}

function mapStateToProps(state) {
    const modalData = selectModalData(state);
    return { ...modalData };
}

export default connect(
    mapStateToProps,
    { hideModal }
)(ModalRoot);
