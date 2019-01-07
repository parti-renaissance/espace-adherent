import React from 'react';
import { connect } from 'react-redux';
import CommentsList from '../../components/CommentsList';
import { selectCurrentIdea, selectCurrentIdeaThread } from '../../redux/selectors/currentIdea';
import { selectIsAuthenticated } from '../../redux/selectors/auth';
import { postComment, deleteComment, approveComment } from '../../redux/thunk/threads';

function IdeaThread(props) {
    const { isAuthenticated, ...otherProps } = props;
    return (
        <React.Fragment>
            <CommentsList
                {...otherProps}
                showForm={isAuthenticated}
                onSendComment={(value, parentId = '') => props.onSendComment(value, props.answerId, parentId)}
                onLoadMore={(parentId = '') => props.onLoadMore(props.answerId, parentId)}
            />
            {!isAuthenticated && (
                <div className="idea-thread__contribute">
                    <p className="idea-thread__contribute__main">
                        Pour ajouter votre contribution,{' '}
                        <a className="idea-thread__contribute__link" href="/connexion">
                            connectez-vous
                        </a>{' '}
                        ou{' '}
                        <a className="idea-thread__contribute__link" href="/adhesion">
                            créez un compte
                        </a>
                    </p>
                </div>
            )}
        </React.Fragment>
    );
}

function mapStateToProps(state, { questionId }) {
    const isAuthenticated = selectIsAuthenticated(state);
    const currentIdea = selectCurrentIdea(state);
    const currentAnswer = currentIdea.answers && currentIdea.answers.find(answer => answer.question.id === questionId);
    const answerId = currentAnswer && currentAnswer.id;
    const answerThread = selectCurrentIdeaThread(state, answerId);
    return {
        comments: [],
        isAuthenticated,
        answerId,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        onSendComment: (content, answerId, threadId) => dispatch(postComment(content, answerId, threadId)),
        onDeleteComment: (commentId, threadId) => dispatch(deleteComment(commentId, threadId)),
        onApproveComment: (commentId, threadId) => dispatch(approveComment(commentId, threadId)),
        onLoadMore: (answerId, threadId) => alert('Load more comment for answer', answerId),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(IdeaThread);
