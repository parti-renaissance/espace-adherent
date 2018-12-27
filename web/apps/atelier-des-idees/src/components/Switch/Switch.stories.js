import React from 'react';
import { storiesOf } from '@storybook/react';
import Switch from '.';

storiesOf('Switch', module)
    .addParameters({ jest: ['Switch'] })
    .add('default', () => <Switch />)
    .add('disabled', () => <Switch disabled={true} />)
    .add('with label', () => <Switch disabled={true} label="Switch me" />);
