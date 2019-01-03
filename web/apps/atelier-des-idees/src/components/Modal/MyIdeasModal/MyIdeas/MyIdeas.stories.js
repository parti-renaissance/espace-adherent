import React from 'react';
import { storiesOf } from '@storybook/react';
import MyIdeas from '.';

const props = {
    ideas: [
        {
            created_at: new Date().toISOString(),
            name:
				'Réduire le nombre d’élèves dans les classes dans les quartiers défavorisés',
            status: 'PENDING',
        },
        {
            created_at: new Date().toISOString(),
            name: 'Ceci est une deuxième idée',
            status: 'PENDING',
        },
        {
            created_at: new Date().toISOString(),
            name: 'Ceci est une troisième idée',
            status: 'PENDING',
        },
        {
            created_at: new Date().toISOString(),
            name: 'Ceci est une quatrième idée',
            status: 'FINALIZED',
        },
        {
            created_at: new Date().toISOString(),
            name: 'Ceci est une cinquième idée',
            status: 'DRAFT',
        },
    ],
};

storiesOf('MyIdeas', module)
    .addParameters({ jest: ['MyIdeas'] })
    .add('default', () => <MyIdeas {...props} />);
