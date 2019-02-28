import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import DeleteIdeaModal from '.';

storiesOf('DeleteIdeaModal', module)
    .addParameters({ jest: ['DeleteIdeaModal'] })
    .add('default', () => (
        <DeleteIdeaModal closeModal={action('close modal')} onConfirmDelete={action('confirm delete')} />
    ));
