import React from 'react';
import { storiesOf } from '@storybook/react';
import MyIdeas from '.';

storiesOf('MyIdeas', module)
    .addParameters({ jest: ['MyIdeas'] })
    .add('default', () => <MyIdeas />);
