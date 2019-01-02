import React from 'react';
import { storiesOf } from '@storybook/react';
import MyContributions from '.';

storiesOf('MyContributions', module)
    .addParameters({ jest: ['MyContributions'] })
    .add('default', () => <MyContributions />);
