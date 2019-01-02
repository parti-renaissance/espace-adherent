import React from 'react';
import { storiesOf } from '@storybook/react';
import MyIdeasModal from '.';

storiesOf('MyIdeasModal', module)
    .addParameters({ jest: ['MyIdeasModal'] })
    .add('default', () => <MyIdeasModal />);
