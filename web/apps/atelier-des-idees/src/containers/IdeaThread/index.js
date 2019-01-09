import React from 'react';
import { connect } from 'react-redux';
import { sortEntitiesByDate } from '../../helpers/entities';
import CommentsList from '../../components/CommentsList';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectCurrentIdea } from '../../redux/selectors/currentIdea';
import { selectAnswerThreads } from '../../redux/selectors/threads';
import { selectIsAuthenticated, selectAuthUser } from '../../redux/selectors/auth';
import { deleteComment, approveComment } from '../../redux/thunk/threads';
import { postCommentToCurrentIdea, fetchNextAnswerThreads } from '../../redux/thunk/currentIdea';

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
    // auth
    const isAuthenticated = selectIsAuthenticated(state);
    const currentUser = selectAuthUser(state);
    // threads
    const currentIdea = selectCurrentIdea(state);
    const currentAnswer = currentIdea.answers && currentIdea.answers.find(answer => answer.question.id === questionId);
    const answerId = currentAnswer && currentAnswer.id;
    const answerThreads = selectAnswerThreads(state, answerId);
    // loading
    // TODO: handle send thread comment
    const sendCommentState = selectLoadingState(state, 'POST_THREAD', answerId);
    return {
        comments: sortEntitiesByDate(answerThreads),
        isAuthenticated,
        answerId,
        isSendingComment: sendCommentState.isFetching,
        currentUserId: currentUser.uuid,
        ownerId: currentIdea.author.uuid,
        total: currentAnswer && currentAnswer.threads.total_items,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        onSendComment: (content, answerId, threadId) => dispatch(postCommentToCurrentIdea(content, answerId, threadId)),
        onDeleteComment: (commentId, threadId) => dispatch(deleteComment(commentId, threadId)),
        onApproveComment: (commentId, threadId) => dispatch(approveComment(commentId, threadId)),
        onLoadMore: (answerId, threadId) => dispatch(fetchNextAnswerThreads(answerId)),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(IdeaThread);
