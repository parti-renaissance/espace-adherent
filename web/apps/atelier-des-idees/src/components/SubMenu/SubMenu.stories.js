import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import SubMenu from '.';

storiesOf('SubMenu', module)
    .addParameters({ jest: ['SubMenu'] })
    .add('default', () => (
        <SubMenu
            onSelect={action('select value')}
            options={[
                { value: 'report', label: 'Signaler', isImportant: true },
                { value: 'save', label: 'Enregistrer' },
            ]}
        />
    ));
