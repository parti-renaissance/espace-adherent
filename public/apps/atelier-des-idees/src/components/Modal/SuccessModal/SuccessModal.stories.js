import React from 'react';
import { storiesOf } from '@storybook/react';
import SuccessModal from '.';

storiesOf('SuccessModal', module)
    .addParameters({ jest: ['SuceesSignal'] })
    .add('default', () => <SuccessModal text="Message de succès" />);
