import React from 'react';
import { storiesOf } from '@storybook/react';
import FormSubmit from '.';

storiesOf('FormSubmit', module)
    .addParameters({ jest: ['FormSubmit'] })
    .add('default', () => <FormSubmit />);
