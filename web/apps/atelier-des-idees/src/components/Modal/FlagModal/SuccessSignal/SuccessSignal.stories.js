import React from 'react';
import { storiesOf } from '@storybook/react';
import SuceesSignal from '.';

storiesOf('SuceesSignal', module)
    .addParameters({ jest: ['SuceesSignal'] })
    .add('default', () => <SuceesSignal />);
