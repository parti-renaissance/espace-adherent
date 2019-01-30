import React from 'react';
import { storiesOf } from '@storybook/react';
import Comment from '.';

const props = {
    id: '0000',
    content:
        'Integer quis nulla nec lectus vulputate cursus. Integer suscipit, ante sit amet convallis volutpat, ipsum nunc condimentum velit, ut tristique leo orci nec risus. Praesent a diam id sem finibus sodales. Nunc vel vulputate arcu. Donec dui lectus, semper et finibus in, maximus et eros. Nulla id rhoncus odio, ac vulputate sem. Suspendisse et mattis diam. Nullam consequat est neque, vel sollicitudin mi vulputate quis.',
    author: { uuid: 'u1', first_name: 'Jean-Charles', last_name: 'F.' },
    createdAt: '29/11/2018 à 16h02',
    verified: false,
    canApprove: false,
};

storiesOf('Comment', module)
    .addParameters({ jest: ['Comment'] })
    .add('default', () => <Comment {...props} />)
    .add('can approve', () => <Comment {...props} canApprove={true} />)
    .add('approved', () => <Comment {...props} canApprove={true} approved={true} />)
    .add('is author', () => <Comment {...props} verified={true} isAuthor={true} />);
