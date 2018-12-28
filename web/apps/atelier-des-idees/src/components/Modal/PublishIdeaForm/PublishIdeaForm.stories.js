import React from 'react';
import { storiesOf } from '@storybook/react';
import PublishIdeaForm from '.';

storiesOf('PublishIdeaForm', module)
    .addParameters({ jest: ['PublishIdeaForm'] })
    .add('default', () => <PublishIdeaForm />);
