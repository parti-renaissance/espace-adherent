import React from 'react';
import { storiesOf } from '@storybook/react';
import PublishIdeaFormModal from '.';

storiesOf('PublishIdeaFormModal', module)
    .addParameters({ jest: ['PublishIdeaFormModal'] })
    .add('default', () => <PublishIdeaFormModal />);
