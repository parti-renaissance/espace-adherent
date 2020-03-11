import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import IdeaPageBase from '../IdeaPageBase';
import { DELETE_IDEA_MODAL, PUBLISH_IDEA_MODAL } from '../../constants/modalTypes';
import { showModal } from '../../redux/actions/modal';
import { initCreateIdeaPage } from '../../redux/thunk/navigation';
import {
    setAutoCompleteTitleIdea,
    saveCurrentIdea,
    publishCurrentIdea,
    deleteCurrentIdea,
    goBackFromCurrentIdea,
} from '../../redux/thunk/currentIdea';
import { selectAuthUser } from '../../redux/selectors/auth';
import { selectGuidelines, selectCurrentIdea } from '../../redux/selectors/currentIdea';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectRepublicanSilences } from '../../redux/selectors/republicanSilence';
import RepublicanSilence from '../../components/RepublicanSilence';

class CreateIdeaPage extends React.Component {
    componentDidMount(e) {
        window.scrollTo(0, 0);
        this.props.initCreateIdeaPage();
    }

    render() {
        if (this.props.isRepublicanSilenceStarted) {
            return <RepublicanSilence />;
        }

        return (
            <IdeaPageBase
                {...this.props}
                isLoading={!this.props.guidelines.length}
                key={`idea-page__${!!this.props.guidelines.length}`}
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
    const silences = selectRepublicanSilences(state);
    const isRepublicanSilenceStarted = Array.isArray(silences) && silences.length > 0;

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
        isAuthenticated: true,
        isSaveSuccess: saveState.isSuccess,
        isSaving: saveState.isFetching,
        autoComplete: state.currentIdea.idea.autoComplete,
        isRepublicanSilenceStarted: isRepublicanSilenceStarted,
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
        autoCompleteTitleIdea: data => dispatch(setAutoCompleteTitleIdea(data)),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(CreateIdeaPage);
