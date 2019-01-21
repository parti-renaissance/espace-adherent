import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import Dropdown from '../../Dropdown';
import icn_checklist from './../../../img/icn_checklist-white.svg';

function Comment(props) {
    return (
        <div
            className={classNames('comments-list__comment', {
                'comments-list__comment--approved': props.approved,
            })}
        >
            <div className="comments-list__comment__infos">
                <div className="comments-list__comment__infos--main">
                    <span className="comments-list__comment__infos__author">
                        {props.author.first_name} {props.author.last_name}
                    </span>
                    <span className="comments-list__comment__infos__date">
                        {`${new Date(props.created_at).toLocaleDateString()} à ${new Date(props.created_at)
                            .toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                            .split(':')
                            .join('h')}`}
                    </span>
                    {props.approved && (
                        <span className="comments-list__comment__infos__approved">
                            <img
                                className="comments-list__comment__infos__approved__icon"
                                src={icn_checklist}
                                alt="Commentaire approuvé"
                            />
                        </span>
                    )}
                </div>
                {props.hasActions && !props.isAuthor && (
                    <Dropdown
                        onSelect={props.onReport}
                        options={[{ value: 'report', label: 'Signaler', isImportant: true }]}
                    />
                )}
            </div>
            <div className="comments-list__comment__content">{props.content}</div>
            {props.hasActions && (
                <div className="comments-list__comment__actions">
                    {props.isAuthor ? (
                        <button className="comments-list__comment__actions__button__delete" onClick={props.onDelete}>
                            Supprimer
                        </button>
                    ) : (
                        <React.Fragment>
                            {props.canApprove && (
                                <button
                                    onClick={props.onApprove}
                                    className={classNames({
                                        'comments-list__comment__actions__button__approved': !props.approved,
                                        'comments-list__comment__actions__button__disapproved': props.approved,
                                    })}
                                >
                                    {props.approved ? 'Désapprouver' : 'Approuver'}
                                </button>
                            )}
                            {props.canAnswer && (
                                <button
                                    className="comments-list__comment__actions__button__answer"
                                    onClick={props.onReply}
                                >
                                    Répondre
                                </button>
                            )}
                        </React.Fragment>
                    )}
                </div>
            )}
        </div>
    );
}

Comment.defaultProps = {
    approved: false,
    hasActions: true,
    isAuthor: false,
    verified: false,
    canAnswer: true,
    canApprove: false,
};

Comment.propTypes = {
    author: PropTypes.shape({
        id: PropTypes.string,
        first_name: PropTypes.string,
        last_name: PropTypes.string,
    }).isRequired,
    content: PropTypes.string.isRequired,
    created_at: PropTypes.string.isRequired, // iso date
    approved: PropTypes.bool,
    canAnswer: PropTypes.bool,
    canApprove: PropTypes.bool,
    isAuthor: PropTypes.bool,
    hasActions: PropTypes.bool,
    onApprove: PropTypes.func,
    onEdit: PropTypes.func,
    onReply: PropTypes.func,
    onDelete: PropTypes.func,
    onReport: PropTypes.func,
};

export default Comment;
