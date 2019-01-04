import { connect } from 'react-redux';
import CommentsList from '../../components/CommentsList';
import { selectCurrentIdea, selectCurrentIdeaThread } from '../../redux/selectors/currentIdea';
import { selectIsAuthenticated } from '../../redux/selectors/auth';
import { postComment, deleteComment, approveComment } from '../../redux/thunk/threads';

function mapStateToProps(state, { questionId }) {
    const isAuthenticated = selectIsAuthenticated(state);
    const currentIdea = selectCurrentIdea(state);
    const currentAnswer = currentIdea.answers.find(answer => answer.question.id === questionId);
    const answerId = currentAnswer && currentAnswer.id;
    const answerThread = selectCurrentIdeaThread(state, answerId);
    return {
        comments: [],
        // showForm: isAuthenticated,
        showForm: false, // TODO: remove and uncomment above
    };
}

function mapDispatchToProps(dispatch) {
    return {
        onSendComment: (content, answerId, threadId) => dispatch(postComment(content, answerId, threadId)),
        onDeleteComment: (commentId, threadId) => dispatch(deleteComment(commentId, threadId)),
        onApproveComment: (commentId, threadId) => dispatch(approveComment(commentId, threadId)),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(CommentsList);
