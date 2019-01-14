import React from 'react';
import { connect } from 'react-redux';
import { sortEntitiesByDate } from '../../helpers/entities';
import { FLAG_MODAL } from '../../constants/modalTypes';
import CommentsList from '../../components/CommentsList';
import { selectLoadingState } from '../../redux/selectors/loading';
import { selectCurrentIdea } from '../../redux/selectors/currentIdea';
import { selectAnswerThreadsWithReplies } from '../../redux/selectors/threads';
import { selectIsAuthenticated, selectAuthUser } from '../../redux/selectors/auth';
import { approveComment, reportComment, fetchNextThreadComments } from '../../redux/thunk/threads';
import {
    postCommentToCurrentIdea,
    removeCommentFromCurrentIdea,
    fetchNextAnswerThreads,
} from '../../redux/thunk/currentIdea';
import { showModal } from '../../redux/actions/modal';

// TODO: reset paging data on unmount
function IdeaThread(props) {
    const { isAuthenticated, ...otherProps } = props;
    return (
        <React.Fragment>
            <CommentsList
                {...otherProps}
                showForm={isAuthenticated}
                onSendComment={(value, parentId = '') => props.onSendComment(value, props.answerId, parentId)}
                onLoadMore={(parentId = '') => props.onLoadMore(props.answerId, parentId)}
                hasActions={isAuthenticated}
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
    const answerThreads = selectAnswerThreadsWithReplies(state, answerId);
    // loading
    const sendThreadState = selectLoadingState(state, 'POST_THREAD', answerId);
    const sendingThreadComments = answerThreads.reduce((acc, thread) => {
        // get thread comment post status for each thread of the answer
        const { isFetching } = selectLoadingState(state, 'POST_THREAD_COMMENT', `${answerId}_${thread.uuid}`);
        if (isFetching) {
            acc.push(thread.uuid);
        }
        return acc;
    }, []);
    return {
        comments: sortEntitiesByDate(answerThreads),
        isAuthenticated,
        answerId,
        isSendingComment: sendThreadState.isFetching,
        sendingReplies: sendingThreadComments,
        currentUserId: currentUser.uuid,
        ownerId: currentIdea.author && currentIdea.author.uuid,
        total: currentAnswer && currentAnswer.threads.total_items,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        onSendComment: (content, answerId, threadId) => dispatch(postCommentToCurrentIdea(content, answerId, threadId)),
        onDeleteComment: (commentId, threadId) => dispatch(removeCommentFromCurrentIdea(commentId, threadId)),
        onApproveComment: (commentId, threadId) => dispatch(approveComment(commentId, threadId)),
        onReportComment: (commentId, threadId) =>
            dispatch(showModal(FLAG_MODAL, { onSubmit: data => dispatch(reportComment(data, commentId, threadId)) })),
        onLoadMore: (answerId, threadId) => {
            if (threadId) {
                dispatch(fetchNextThreadComments(threadId));
            } else {
                dispatch(fetchNextAnswerThreads(answerId));
            }
        },
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(IdeaThread);
