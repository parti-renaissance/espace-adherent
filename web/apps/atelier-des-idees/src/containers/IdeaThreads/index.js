import { connect } from 'react-redux';
import CommentsList from '../../components/CommentsList';
import { selectCurrentIdea, selectCurrentIdeaThread } from '../../redux/selectors/currentIdea';
import { selectIsAuthenticated } from '../../redux/selectors/auth';

function mapStateToProps(state, { questionId }) {
    const isAuthenticated = selectIsAuthenticated(state);
    const currentIdea = selectCurrentIdea(state);
    const currentAnswer = currentIdea.answers.find(answer => answer.question.id === questionId);
    const answerId = currentAnswer && currentAnswer.id;
    const answerThread = selectCurrentIdeaThread(state, answerId);
    return {
        comments: [],
        showForm: isAuthenticated,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        onSendComment: (value, threadId) => alert('Send comment'),
        onDeleteComment: (commentId, threadId) => alert('Delete comment'),
        onApproveComment: (commentId, threadId) => alert('Approve comment'),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(CommentsList);
