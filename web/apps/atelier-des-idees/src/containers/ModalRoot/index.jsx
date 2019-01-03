import React from 'react';
import { connect } from 'react-redux';
import ReactModal from 'react-modal';
import { hideModal } from '../../redux/actions/modal';
import { selectModalData } from '../../redux/selectors/modal';

// components
import ReportsModal from '../../components/Modal/ReportsModal';
import PublishIdeaFormModal from '../../containers/PublishIdeaFormModal';
import DeleteIdeaModal from '../../components/Modal/DeleteIdeaModal';
// import MyIdeasModal from '../../components/Modal/MyIdeasModal';
import MyIdeasContainer from '../../containers/MyIdeas';

const MODAL_COMPONENTS = {
    // to use a modal, just add it below with its corresponding type
    // ex:
    // modalTypes.TEST_MODAL: TestModal,
    REPORTS_MODAL: ReportsModal,
    PUBLISH_IDEA_MODAL: PublishIdeaFormModal,
    DELETE_IDEA_MODAL: DeleteIdeaModal,
    MY_IDEAS_MODAL: MyIdeasContainer,
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
                className="modal"
                overlayClassName="modal-overlay"
                isOpen={isOpen}
                onRequestClose={this.closeModal}
                ariaHideApp={false}
            >
                <button className="modal-closeBtn" onClick={this.closeModal}>
                    <img src="/assets/img/icn_close.svg" />
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
