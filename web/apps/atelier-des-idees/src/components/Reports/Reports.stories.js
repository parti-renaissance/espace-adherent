import React from 'react';
import { storiesOf } from '@storybook/react';
import Reports from '.';

storiesOf('Reports', module)
    .addParameters({ jest: ['Reports'] })
    .add('default', () => <Reports />);
