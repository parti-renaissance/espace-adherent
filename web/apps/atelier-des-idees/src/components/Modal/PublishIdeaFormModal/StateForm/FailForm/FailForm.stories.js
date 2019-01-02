import React from 'react';
import { storiesOf } from '@storybook/react';
import FailForm from '.';

storiesOf('FailForm', module)
    .addParameters({ jest: ['FailForm'] })
    .add('default', () => <FailForm />);
