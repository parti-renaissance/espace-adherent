import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import CommentsList from '.';

const props = {
    comments: [
        {
            id: '0000',
            content: 'Commentaire 1',
            author: { id: 'u1', name: 'Jean-Charles F.' },
            createdAt: '29/11/2018 à 16h02',
            verified: true,
            replies: [
                {
                    id: '2222',
                    content: 'Réponse 1',
                    author: { id: 'u2', name: 'Micheline B.' },
                    createdAt: '29/11/2018 à 18h04',
                    verified: false,
                },
            ],
        },
        {
            id: '1111',
            content: 'Commentaire 2',
            author: { id: 'u2', name: 'Micheline B.' },
            createdAt: '29/11/2018 à 18h04',
            verified: false,
            replies: [],
        },
    ],
};

storiesOf('CommentsList', module)
    .addParameters({ jest: ['CommentsList'] })
    .add('default', () => (
        <CommentsList
            comments={props.comments}
            onSendComment={(comment, parentId = undefined) =>
                action(comment, parentId)
            }
            onDeleteComment={commentId => action('delete comment', commentId)}
            ownerId="u1"
        />
    ));
