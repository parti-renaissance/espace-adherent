import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import IdeaPageBase from '../IdeaPageBase';
import { DELETE_IDEA_MODAL } from '../../constants/modalTypes';
import { showModal } from '../../redux/actions/modal';
import { deleteIdea } from '../../redux/thunk/ideas';
import { selectAuthUser } from '../../redux/selectors/auth';

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
    const currentUser = selectAuthUser(state);
    // TODO: uncomment
    // const idea = selectCurrentIdea(state)
    const metadata = { authorName: currentUser.name, createdAt: new Date().toLocaleDateString() };
    return {
        // idea,
        isAuthor: true,
        metadata,
        isEditing: true,
    };
}

function mapDispatchToProps(dispatch, ownProps) {
    // TODO: replace with actual action creators
    return {
        initIdeaPage: () => {
            const { id } = ownProps.match.params;
            // TODO: dispatch thunk
        },
        onBackClicked: () => alert('Retour'),
        onPublishClicked: () => alert('Publier'),
        onDeleteClicked: () =>
            dispatch(
                showModal(DELETE_IDEA_MODAL, {
                    onConfirmDelete: () => dispatch(deleteIdea()),
                })
            ),
        onSaveClicked: () => alert('Enregistrer'),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(IdeaPage);
