import React from 'react';
import { storiesOf } from '@storybook/react';
import MovementIdeas from '.';

storiesOf('MovementIdeas', module)
    .addParameters({ jest: ['MovementIdeas'] })
    .add('default', () => <MovementIdeas />);