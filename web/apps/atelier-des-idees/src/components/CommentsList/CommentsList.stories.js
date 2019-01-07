import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import CommentsList from '.';

const comments = [
    {
        uuid: '0000',
        content: 'Commentaire 1',
        author: { uuid: 'u1', first_name: 'Jean-Charles', last_name: 'F.' },
        createdAt: '29/11/2018 à 16h02',
        verified: true,
        replies: [
            {
                uuid: '2222',
                content: 'Réponse 1',
                author: { uuid: 'u2', first_name: 'Micheline', last_name: 'B.' },
                createdAt: '29/11/2018 à 18h04',
                verified: false,
            },
        ],
        nbReplies: 3,
    },
    {
        uuid: '1111',
        content: 'Commentaire 2',
        author: { uuid: 'u2', first_name: 'Micheline', last_name: 'B.' },
        createdAt: '29/11/2018 à 18h04',
        verified: false,
        replies: [],
    },
];

storiesOf('CommentsList', module)
    .addParameters({ jest: ['CommentsList'] })
    .add('default', () => (
        <CommentsList
            comments={comments}
            onSendComment={action('send comment')}
            onDeleteComment={action('delete comment')}
            onEditComment={action('edit comment')}
            onApprovedComment={action('edit comment')}
            ownerId="u1"
            onLoadMore={action('Load more comments')}
        />
    ));
