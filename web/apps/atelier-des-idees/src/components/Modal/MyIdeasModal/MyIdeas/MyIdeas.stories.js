import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import MyIdeas from '.';

const props = {
    ideas: [
        {
            uuid: '0000',
            created_at: new Date().toISOString(),
            name: 'Réduire le nombre d’élèves dans les classes dans les quartiers défavorisés',
            status: 'PENDING',
        },
        {
            uuid: '0000',
            created_at: new Date().toISOString(),
            name: 'Ceci est une deuxième idée',
            status: 'PENDING',
        },
        {
            uuid: '0000',
            created_at: new Date().toISOString(),
            name: 'Ceci est une troisième idée',
            status: 'PENDING',
        },
        {
            uuid: '0000',
            created_at: new Date().toISOString(),
            name: 'Ceci est une quatrième idée',
            status: 'FINALIZED',
        },
        {
            uuid: '0000',
            created_at: new Date().toISOString(),
            name: 'Ceci est une cinquième idée',
            status: 'DRAFT',
        },
    ],
};

storiesOf('MyIdeas', module)
    .addParameters({ jest: ['MyIdeas'] })
    .add('default', () => <MyIdeas {...props} onDeleteIdea={action('delete idea')} />);
