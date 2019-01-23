import React from 'react';
import { storiesOf } from '@storybook/react';
import Comment from '.';

const props = {
    id: '0000',
    content:
		'Integer quis nulla nec lectus vulputate cursus. Integer suscipit, ante sit amet convallis volutpat, ipsum nunc condimentum velit, ut tristique leo orci nec risus. Praesent a diam id sem finibus sodales. Nunc vel vulputate arcu. Donec dui lectus, semper et finibus in, maximus et eros. Nulla id rhoncus odio, ac vulputate sem. Suspendisse et mattis diam. Nullam consequat est neque, vel sollicitudin mi vulputate quis.',
    author: { id: 'u1', name: 'Jean-Charles F.' },
    createdAt: '29/11/2018 à 16h02',
    verified: false,
    replies: [
        {
            id: '2222',
            content: 'Réponse 1',
            author: { id: 'u2', name: 'Micheline B.' },
            createdAt: '29/11/2018 à 18h04',
            verified: false,
        },
    ],
    canApprove: false,
};

storiesOf('Comment', module)
    .addParameters({ jest: ['Comment'] })
    .add('default', () => <Comment {...props} />)
    .add('can approve mode', () => <Comment {...props} canApprove={true} />)
    .add('can disapproved mode', () => (
        <Comment {...props} canApprove={true} verified={true} />
    ))
    .add('is author', () => (
        <Comment {...props} verified={true} isAuthor={true} />
    ));
