import React from 'react';
import { storiesOf } from '@storybook/react';
import App from './App';

storiesOf('App', module)
    .addParameters({ jest: ['App'] })
    .add('default', () => <App />);
