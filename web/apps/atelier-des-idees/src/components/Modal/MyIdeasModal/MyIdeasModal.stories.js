import React from 'react';
import { storiesOf } from '@storybook/react';
import MyIdeasModal from '.';

const props = {
    my_ideas: [
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
    my_contribs: [
        {
            created_at: new Date().toISOString(),
            name: 'Réduire le nombre d’élèves dans les classes dans les quartiers défavorisés',
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

storiesOf('MyIdeasModal', module)
    .addParameters({ jest: ['MyIdeasModal'] })
    .add('default', () => <MyIdeasModal {...props} />);
