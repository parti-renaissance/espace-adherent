import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import IdeaPageBase from '../IdeaPageBase';
import { DELETE_IDEA_MODAL, PUBLISH_IDEA_MODAL } from '../../constants/modalTypes';
import { showModal } from '../../redux/actions/modal';
import { initCreateIdeaPage } from '../../redux/thunk/navigation';
import { saveCurrentIdea, deleteCurrentIdea, goBackFromCurrentIdea } from '../../redux/thunk/currentIdea';
import { publishCurrentIdea } from '../../redux/thunk/currentIdea';
import { selectAuthUser } from '../../redux/selectors/auth';
import { selectGuidelines } from '../../redux/selectors/currentIdea';

class CreateIdeaPage extends React.Component {
    componentDidMount() {
        this.props.initCreateIdeaPage();
    }

    render() {
        return this.props.guidelines.length ? <IdeaPageBase {...this.props} /> : null;
    }
}

CreateIdeaPage.propTypes = {
    initCreateIdeaPage: PropTypes.func.isRequired,
};

function mapStateToProps(state) {
    const currentUser = selectAuthUser(state);
    const guidelines = selectGuidelines(state);
    const idea = {
        authorName: `${currentUser.firstName} ${currentUser.lastName}`,
        status: 'DRAFT',
    };
    return {
        idea,
        guidelines,
        isAuthor: true,
    };
}

function mapDispatchToProps(dispatch) {
    // TODO: replace with actual action creators
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
