import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import Dropdown from '.';

storiesOf('Dropdown', module)
    .addParameters({ jest: ['Dropdown'] })
    .add('default', () => (
        <Dropdown
            onSelect={action('select value')}
            options={[
                { value: 'report', label: 'Signaler', isImportant: true },
                { value: 'save', label: 'Enregistrer' },
            ]}
        />
    ));
