import React from 'react';
import { storiesOf } from '@storybook/react';
import ErrorModal from '.';

storiesOf('ErrorModal', module)
    .addParameters({ jest: ['SuceesSignal'] })
    .add('default', () => <ErrorModal text="Message de succès" />);
