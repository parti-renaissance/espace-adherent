import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import CreateIdeaActions from '.';

storiesOf('CreateIdeaActions', module)
    .addParameters({ jest: ['CreateIdeaActions'] })
    .add('default', () => (
        <CreateIdeaActions
            onDeleteClicked={action('delete clicked')}
            onPublishClicked={action('publish clicked')}
            onSaveClicked={action('save clicked')}
        />
    ))
    .add('footer mode', () => (
        <CreateIdeaActions
            onDeleteClicked={action('delete clicked')}
            onPublishClicked={action('publish clicked')}
            onSaveClicked={action('save clicked')}
            mode="footer"
        />
    ));
