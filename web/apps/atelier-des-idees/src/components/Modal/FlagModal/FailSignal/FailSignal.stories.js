import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import FailSignal from '.';

storiesOf('FailSignal', module)
    .addParameters({ jest: ['FailSignal'] })
    .add('default', () => <FailSignal submitAgain={action('Submit again')} />);
