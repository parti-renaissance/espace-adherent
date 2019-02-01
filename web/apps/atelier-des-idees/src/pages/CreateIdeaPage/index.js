import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import IdeaPageBase from '../IdeaPageBase';
import { DELETE_IDEA_MODAL, PUBLISH_IDEA_MODAL } from '../../constants/modalTypes';
import { showModal } from '../../redux/actions/modal';
import { initCreateIdeaPage } from '../../redux/thunk/navigation';
import {
    saveCurrentIdea,
    publishCurrentIdea,
    deleteCurrentIdea,
    goBackFromCurrentIdea,
} from '../../redux/thunk/currentIdea';
import { selectAuthUser } from '../../redux/selectors/auth';
import { selectGuidelines, selectCurrentIdea } from '../../redux/selectors/currentIdea';
import { selectLoadingState } from '../../redux/selectors/loading';

class CreateIdeaPage extends React.Component {
    componentDidMount() {
        window.scrollTo(0, 0);
        this.props.initCreateIdeaPage();
    }

    render() {
        return (
            <IdeaPageBase
                {...this.props}
                isLoading={!this.props.guidelines.length}
                key={`create-idea-page__${!!this.props.guidelines.length}`}
            />
        );
    }
}

CreateIdeaPage.propTypes = {
    initCreateIdeaPage: PropTypes.func.isRequired,
};

function mapStateToProps(state) {
    const currentUser = selectAuthUser(state);
    const currentIdea = selectCurrentIdea(state);
    const saveState = selectLoadingState(state, 'SAVE_CURRENT_IDEA', currentIdea.uuid);
    const guidelines = selectGuidelines(state);
    const idea = {
        ...currentIdea,
        authorName: `${currentUser.firstName} ${currentUser.lastName}`,
        // use current idea's status to update page when idea has been published
        status: currentIdea.status || 'DRAFT',
    };
    return {
        idea,
        guidelines,
        isAuthor: true,
        isSaveSuccess: saveState.isSuccess,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        initCreateIdeaPage: () => {
            dispatch(initCreateIdeaPage());
        },
        onBackClicked: () => dispatch(goBackFromCurrentIdea()),
        onPublishIdea: data =>
            dispatch(
                showModal(PUBLISH_IDEA_MODAL, {
                    submitForm: ideaData => dispatch(publishCurrentIdea({ ...ideaData, ...data })),
                })
            ),
        onDeleteClicked: () =>
            dispatch(
                showModal(DELETE_IDEA_MODAL, {
                    onConfirmDelete: () => dispatch(deleteCurrentIdea()),
                })
            ),
        onSaveIdea: data => dispatch(saveCurrentIdea(data)),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(CreateIdeaPage);
