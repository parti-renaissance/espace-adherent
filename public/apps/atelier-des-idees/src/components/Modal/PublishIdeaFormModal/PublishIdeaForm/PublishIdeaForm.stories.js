import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import PublishIdeaForm from '.';

const props = {
    themeOptions: [
        { value: 'agriculture', label: 'Agriculture' },
        { value: 'education', label: 'Education' },
        { value: 'culture', label: 'Culture' },
        { value: 'defense', label: 'Défense' },
        { value: 'parity', label: 'Parité' },
    ],
    localityOptions: [{ value: 'national', label: 'National' }, { value: 'european', label: 'Européen' }],
    authorOptions: [{ value: 'alone', label: 'Non' }, { value: 'committee', label: 'Oui' }],
    committeeOptions: [{ value: 'comittee_1', label: 'Comité 1' }, { value: 'comittee_2', label: 'Comité 2' }],
    difficultiesOptions: [{ value: 'juridique', label: 'Juridique' }, { value: 'finance', label: 'Finance' }],
    isSubmitSuccess: false,
    isSubmitError: false,
};

storiesOf('PublishIdeaFormModal/PublishIdeaForm', module)
    .addParameters({ jest: ['PublishIdeaForm'] })
    .add('default', () => <PublishIdeaForm {...props} submitForm={action('Send form')} />)
    .add('submitting', () => <PublishIdeaForm {...props} submitForm={action('Send form')} isSubmitting={true} />);
