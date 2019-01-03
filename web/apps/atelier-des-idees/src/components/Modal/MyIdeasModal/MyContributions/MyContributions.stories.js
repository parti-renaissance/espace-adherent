import React from 'react';
import { storiesOf } from '@storybook/react';
import MyContributions from '.';

const props = {
    ideas: [
        {
            created_at: new Date().toISOString(),
            name:
				'Réduire le nombre d’élèves dans les classes dans les quartiers défavorisés',
        },
        {
            created_at: new Date().toISOString(),
            name: 'Ceci est une deuxième idée',
        },
        {
            created_at: new Date().toISOString(),
            name: 'Ceci est une troisième idée',
        },
        {
            created_at: new Date().toISOString(),
            name: 'Ceci est une quatrième idée',
        },
    ],
};

storiesOf('MyContributions', module)
    .addParameters({ jest: ['MyContributions'] })
    .add('default', () => <MyContributions {...props} />);
