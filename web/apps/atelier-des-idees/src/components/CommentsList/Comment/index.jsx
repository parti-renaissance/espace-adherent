import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

function Comment(props) {
    return (
        <div
            className={classNames('comments-list__comment', {
                'comments-list__comment--approved': props.verified,
            })}
        >
            <div className="comments-list__comment__infos">
                <span className="comments-list__comment__infos__author">
                    {props.author.name}
                </span>
                <span className="comments-list__comment__infos__date">
                    {props.createdAt}
                </span>
                {props.verified && (
                    <span className="comments-list__comment__infos__approved">
                        <img
                            className="comments-list__comment__infos__approved__icon"
                            src="/assets/img/icn_checklist-white.svg"
                        />
                    </span>
                )}
            </div>
            <div className="comments-list__comment__content">{props.content}</div>
            {props.hasActions && (
                <div className="comments-list__comment__actions">
                    {props.isAuthor ? (
                        <React.Fragment>
                            <button
                                className="comments-list__comment__actions__button__delete"
                                onClick={props.onDelete}
                            >
								Supprimer
                            </button>
                            <button
                                className="comments-list__comment__actions__button__edit"
                                onClick={props.onEdit}
                            >
								Modifier
                            </button>
                        </React.Fragment>
                    ) : (
                        <React.Fragment>
                            {props.canApprove &&
								(props.verified ? (
								    <button
								        onClick={props.onApproved}
								        className="comments-list__comment__actions__button__disapproved"
								    >
										Désapprouver
								    </button>
								) : (
								    <button
								        onClick={props.onApproved}
								        className="comments-list__comment__actions__button__approved"
								    >
										Approuver
								    </button>
								))}
                            <button
                                className="comments-list__comment__actions__button__answer"
                                onClick={props.onReply}
                            >
								Répondre
                            </button>
                        </React.Fragment>
                    )}
                </div>
            )}
        </div>
    );
}

Comment.defaultProps = {
    hasActions: true,
    isAuthor: false,
    verified: false,
    canApprove: false,
};

Comment.propTypes = {
    author: PropTypes.shape({ id: PropTypes.string, name: PropTypes.string })
        .isRequired,
    content: PropTypes.string.isRequired,
    createdAt: PropTypes.string.isRequired, // iso date
    verified: PropTypes.bool,
    canApprove: PropTypes.bool,
    isAuthor: PropTypes.bool,
    hasActions: PropTypes.bool,
    onApproved: PropTypes.func,
    onEdit: PropTypes.func,
    onReply: PropTypes.func,
    onDelete: PropTypes.func,
};

export default Comment;
