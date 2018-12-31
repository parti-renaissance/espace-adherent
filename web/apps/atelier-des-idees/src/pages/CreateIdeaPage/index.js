import { connect } from 'react-redux';
import IdeaPageBase from '../IdeaPageBase';
import { DELETE_IDEA_MODAL } from '../../constants/modalTypes';
import { showModal } from '../../redux/actions/modal';
import { deleteIdea } from '../../redux/thunk/ideas';

function mapStateToProps(state) {
    // const currentUser = getCurrentUser(state)
    // const metadata = { authorName: currentUser.name, createdAt: new Date().toLocaleDateString() };
    // TODO: uncomment above and remove below
    const metadata = {
        authorName: 'Killian Mbappé',
        createdAt: new Date().toLocaleDateString(),
    };
    return {
        isAuthor: true,
        metadata,
        isEditing: true,
    };
}

function mapDispatchToProps(dispatch) {
    // TODO: replace with actual action creators
    return {
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
)(IdeaPageBase);
