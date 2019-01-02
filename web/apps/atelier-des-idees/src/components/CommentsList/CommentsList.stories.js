import React from 'react';
import { storiesOf } from '@storybook/react';
import CommentsList from '.';

storiesOf('CommentsList', module)
    .addParameters({ jest: ['CommentsList'] })
    .add('default', () => <CommentsList />);
