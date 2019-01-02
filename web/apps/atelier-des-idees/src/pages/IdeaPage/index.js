import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import IdeaPageBase from '../IdeaPageBase';
import { DELETE_IDEA_MODAL } from '../../constants/modalTypes';
import { showModal } from '../../redux/actions/modal';
import { initIdeaPage } from '../../redux/thunk/navigation';
import { deleteCurrentIdea, goBackFromCurrentIdea } from '../../redux/thunk/currentIdea';
import { selectAuthUser } from '../../redux/selectors/auth';
import { selectCurrentIdea } from '../../redux/selectors/currentIdea';

class IdeaPage extends React.Component {
    componentDidMount() {
        this.props.initIdeaPage();
    }

    render() {
        return <IdeaPageBase {...this.props} />;
    }
}

IdeaPage.propTypes = {
    initIdeaPage: PropTypes.func.isRequired,
};

function mapStateToProps(state) {
    // TODO: handle loading and error
    const currentUser = selectAuthUser(state);
    // get and format current idea
    const idea = selectCurrentIdea(state);
    const { author, created_at, ...ideaData } = idea;
    const formattedIdea = {
        ...ideaData,
        authorName: author ? `${author.first_name} ${author.last_name}` : '',
        createdAt: created_at && new Date(created_at).toLocaleDateString(),
    };
    return {
        idea: formattedIdea,
        isAuthor: !!author && author.uuid === currentUser.uuid,
    };
}

function mapDispatchToProps(dispatch, ownProps) {
    // TODO: replace with actual action creators
    return {
        initIdeaPage: () => {
            const { id } = ownProps.match.params;
            dispatch(initIdeaPage(id));
        },
        onBackClicked: () => dispatch(goBackFromCurrentIdea()),
        onPublishClicked: () => alert('Publier'),
        onDeleteClicked: () =>
            dispatch(
                showModal(DELETE_IDEA_MODAL, {
                    onConfirmDelete: () => dispatch(deleteCurrentIdea()),
                })
            ),
        onSaveClicked: () => alert('Enregistrer'),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(IdeaPage);
