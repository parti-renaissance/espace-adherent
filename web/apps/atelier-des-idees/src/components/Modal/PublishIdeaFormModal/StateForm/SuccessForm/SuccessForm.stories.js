import React from 'react';
import { storiesOf } from '@storybook/react';
import SucessForm from '.';

storiesOf('SucessForm', module)
    .addParameters({ jest: ['SucessForm'] })
    .add('default', () => <SucessForm id="0000" />);
