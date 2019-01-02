import { connect } from 'react-redux';
import IdeaPageBase from '../IdeaPageBase';
import { DELETE_IDEA_MODAL } from '../../constants/modalTypes';
import { showModal } from '../../redux/actions/modal';
import { saveCurrentIdea, deleteCurrentIdea, goBackFromCurrentIdea } from '../../redux/thunk/currentIdea';
import { selectAuthUser } from '../../redux/selectors/auth';

function mapStateToProps(state) {
    const currentUser = selectAuthUser(state);
    const idea = {
        authorName: `${currentUser.first_name} ${currentUser.last_name}`,
        createdAt: new Date().toLocaleDateString(),
        status: 'DRAFT',
    };
    return {
        idea,
        isAuthor: true,
    };
}

function mapDispatchToProps(dispatch) {
    // TODO: replace with actual action creators
    return {
        onBackClicked: () => dispatch(goBackFromCurrentIdea()),
        onPublishClicked: () => alert('Publier'),
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
)(IdeaPageBase);
