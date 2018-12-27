import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import Select from '.';

const props = {
    subtitle:
		'Ajoutez plusieurs désserts si besoin, dans l\'ordre de leur importance',
    options: [
        { value: 'chocolate', label: 'Chocolate' },
        { value: 'strawberry', label: 'Strawberry' },
        { value: 'vanilla', label: 'Vanilla' },
    ],
    placeholder: 'Choisir un déssert',
    isClearable: true,
};

storiesOf('Select', module)
    .addParameters({ jest: ['Select'] })
    .add('default', () => (
        <Select {...props} onSelected={action('Selected option')} />
    ))
    .add('multi select', () => (
        <Select {...props} onSelected={action('Selected option')} isMulti={true} />
    ))
    .add('with error', () => (
        <Select
            {...props}
            onSelected={action('Selected option')}
            error="Message erreur"
        />
    ));
