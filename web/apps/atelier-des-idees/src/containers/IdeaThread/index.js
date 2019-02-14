import React from 'react';
import { connect } from 'react-redux';
import { sortEntitiesByDate } from '../../helpers/entities';
import { FLAG_MODAL, DELETE_COMMENT_MODAL } from '../../constants/modalTypes';
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
import { resetThreadPagingData } from '../../redux/actions/threads';

class IdeaThread extends React.Component {
    componentWillUnmount() {
        this.props.unmountIdeaThread();
    }

    render() {
        const { isAuthenticated, ...otherProps } = this.props;
        return (
            <CommentsList
                {...otherProps}
                onSendComment={(value, parentId = '') => this.props.onSendComment(value, this.props.answerId, parentId)}
                onLoadMore={(parentId = '') => this.props.onLoadMore(this.props.answerId, parentId)}
                isAuthenticated={isAuthenticated}
            />
        );
    }
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
    const totalThreads = currentAnswer && currentAnswer.threads.total_items; // total number of threads
    const totalThreadComments = answerThreads.reduce((acc, thread) => acc + thread.nbReplies, 0); // total number of thread comments
    const total = totalThreads + totalThreadComments; // total number of contributions (threads + comments)
    return {
        comments: sortEntitiesByDate(answerThreads),
        isAuthenticated,
        answerId,
        isSendingComment: sendThreadState.isFetching,
        sendingReplies: sendingThreadComments,
        currentUserId: currentUser.uuid,
        ownerId: currentIdea.author && currentIdea.author.uuid,
        total,
        totalComments: totalThreads,
        withCGU: !currentUser.comments_cgu_accepted,
    };
}

function mapDispatchToProps(dispatch) {
    return {
        onSendComment: (content, answerId, threadId) => dispatch(postCommentToCurrentIdea(content, answerId, threadId)),
        onDeleteComment: (commentId, threadId) =>
            dispatch(
                showModal(DELETE_COMMENT_MODAL, {
                    onConfirmDelete: () => dispatch(removeCommentFromCurrentIdea(commentId, threadId)),
                })
            ),
        onApproveComment: (commentId, threadId) => dispatch(approveComment(commentId, threadId)),
        onReportComment: (commentId, threadId) =>
            dispatch(
                showModal(FLAG_MODAL, {
                    onSubmit: data => dispatch(reportComment(data, commentId, threadId)),
                    id: commentId,
                })
            ),
        onLoadMore: (answerId, threadId) => {
            if (threadId) {
                dispatch(fetchNextThreadComments(threadId));
            } else {
                dispatch(fetchNextAnswerThreads(answerId));
            }
        },
        unmountIdeaThread: () => dispatch(resetThreadPagingData()),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(IdeaThread);
