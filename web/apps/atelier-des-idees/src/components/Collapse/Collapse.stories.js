import React from 'react';
import { storiesOf } from '@storybook/react';
import Collapse from '.';

storiesOf('Collapse', module)
    .addParameters({ jest: ['Collapse'] })
    .add('default', () => <Collapse />);
