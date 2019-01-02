import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import FailForm from '.';

storiesOf('FailForm', module)
    .addParameters({ jest: ['FailForm'] })
    .add('default', () => <FailForm submitAgain={action('Submit again')} />);
