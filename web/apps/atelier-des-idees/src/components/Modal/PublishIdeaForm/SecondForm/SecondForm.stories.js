import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import SecondForm from '.';

const props = {
    authorOptions: [
        { value: 'alone', label: 'Seul' },
        { value: 'committee', label: 'Mon comité' },
    ],
    difficultiesOptions: [
        { value: 'juridique', label: 'Juridique' },
        { value: 'finance', label: 'Finance' },
    ],
};

storiesOf('SecondForm', module)
    .addParameters({ jest: ['SecondForm'] })
    .add('default', () => (
        <SecondForm {...props} onSubmit={action('second form')} />
    ));
