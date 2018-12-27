import React from 'react';
import { storiesOf } from '@storybook/react';
import FirstForm from '.';

storiesOf('FirstForm', module)
    .addParameters({ jest: ['FirstForm'] })
    .add('default', () => <FirstForm />);
