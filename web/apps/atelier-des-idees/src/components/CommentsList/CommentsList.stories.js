import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import CommentsList from '.';

const comments = [
    {
        uuid: '0000',
        content: 'Commentaire 1',
        author: { uuid: 'u1', first_name: 'Jean-Charles', last_name: 'F.' },
        created_at: new Date().toISOString(),
        verified: true,
        replies: [
            {
                uuid: '2222',
                content: 'Réponse 1',
                author: { uuid: 'u2', first_name: 'Micheline', last_name: 'B.' },
                created_at: new Date().toISOString(),
                verified: false,
            },
        ],
        nbReplies: 4,
    },
    {
        uuid: '1111',
        content: 'Commentaire 2',
        author: { uuid: 'u2', first_name: 'Micheline', last_name: 'B.' },
        created_at: new Date().toISOString(),
        verified: false,
        replies: [],
    },
];

storiesOf('CommentsList', module)
    .addParameters({ jest: ['CommentsList'] })
    .add('default', () => (
        <CommentsList
            isAuthenticated={true}
            comments={comments}
            onSendComment={action('send comment')}
            onDeleteComment={action('delete comment')}
            onEditComment={action('edit comment')}
            onApprovedComment={action('edit comment')}
            ownerId="u1"
            onLoadMore={action('Load more comments')}
            currentUserId="u100"
        />
    ))
    .add('sending comment', () => (
        <CommentsList
            isAuthenticated={true}
            comments={comments}
            onSendComment={action('send comment')}
            onDeleteComment={action('delete comment')}
            onEditComment={action('edit comment')}
            onApprovedComment={action('edit comment')}
            ownerId="u1"
            onLoadMore={action('Load more comments')}
            currentUserId="u100"
            isSendingComment={true}
        />
    ))
    .add('hide form', () => (
        <CommentsList
            isAuthenticated={true}
            comments={comments}
            onSendComment={action('send comment')}
            onDeleteComment={action('delete comment')}
            onEditComment={action('edit comment')}
            onApprovedComment={action('edit comment')}
            ownerId="u1"
            onLoadMore={action('Load more comments')}
            currentUserId="u100"
            showForm={false}
        />
    ))
    .add('load more', () => (
        <CommentsList
            isAuthenticated={true}
            comments={comments}
            onSendComment={action('send comment')}
            onDeleteComment={action('delete comment')}
            onEditComment={action('edit comment')}
            onApprovedComment={action('edit comment')}
            ownerId="u1"
            onLoadMore={action('Load more comments')}
            currentUserId="u100"
            total={comments.length * 2}
        />
    ))
    .add('is owner', () => (
        <CommentsList
            isAuthenticated={true}
            comments={comments}
            onSendComment={action('send comment')}
            onDeleteComment={action('delete comment')}
            onEditComment={action('edit comment')}
            onApprovedComment={action('edit comment')}
            ownerId="u1"
            onLoadMore={action('Load more comments')}
            currentUserId="u1"
            total={comments.length * 2}
        />
    ))
    .add('with CGU', () => (
        <CommentsList
            isAuthenticated={true}
            comments={comments}
            onSendComment={action('send comment')}
            onDeleteComment={action('delete comment')}
            onEditComment={action('edit comment')}
            onApprovedComment={action('edit comment')}
            ownerId="u1"
            onLoadMore={action('Load more comments')}
            currentUserId="u100"
            total={comments.length * 2}
            withCGU={true}
        />
    ))
    .add('is not authenticated', () => (
        <CommentsList
            comments={comments}
            onSendComment={action('send comment')}
            onDeleteComment={action('delete comment')}
            onEditComment={action('edit comment')}
            onApprovedComment={action('edit comment')}
            ownerId="u1"
            onLoadMore={action('Load more comments')}
            currentUserId="u1"
            total={comments.length * 2}
        />
    ))
    .add('without comments', () => (
        <CommentsList
            isAuthenticated={true}
            comments={[]}
            onSendComment={action('send comment')}
            onDeleteComment={action('delete comment')}
            onEditComment={action('edit comment')}
            onApprovedComment={action('edit comment')}
            ownerId="u1"
            onLoadMore={action('Load more comments')}
            currentUserId="u100"
            total={comments.length * 2}
        />
    ))
    .add('without comments / not authenticated', () => (
        <CommentsList
            comments={[]}
            onSendComment={action('send comment')}
            onDeleteComment={action('delete comment')}
            onEditComment={action('edit comment')}
            onApprovedComment={action('edit comment')}
            ownerId="u1"
            onLoadMore={action('Load more comments')}
            currentUserId="u100"
            total={comments.length * 2}
        />
    ));
