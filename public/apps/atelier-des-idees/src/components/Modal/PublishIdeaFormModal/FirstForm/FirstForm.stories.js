import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import FirstForm from '.';

const props = {
    themeOptions: [
        { value: 'agriculture', label: 'Agriculture' },
        { value: 'education', label: 'Education' },
        { value: 'culture', label: 'Culture' },
        { value: 'defense', label: 'Défense' },
        { value: 'parity', label: 'Parité' },
    ],
    localityOptions: [{ value: 'national', label: 'National' }, { value: 'european', label: 'Européen' }],
};

storiesOf('PublishIdeaFormModal/FirstForm', module)
    .addParameters({ jest: ['FirstForm'] })
    .add('default', () => <FirstForm {...props} onSubmit={action('first form')} />);
