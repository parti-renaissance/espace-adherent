import React from 'react';
import { storiesOf } from '@storybook/react';
import MyContributions from '.';

const props = {
    ideas: [
        {
            uuid: 'krjfsd',
            created_at: new Date().toISOString(),
            name: 'Réduire le nombre d’élèves dans les classes dans les quartiers défavorisés',
        },
        {
            uuid: 'sdlfnlksdlfk',
            created_at: new Date().toISOString(),
            name: 'Ceci est une deuxième idée',
        },
        {
            uuid: 'sdlfjpdsjpofj',
            created_at: new Date().toISOString(),
            name: 'Ceci est une troisième idée',
        },
        {
            uuid: 'dlf,lv,lkc,',
            created_at: new Date().toISOString(),
            name: 'Ceci est une quatrième idée',
        },
    ],
};

storiesOf('MyContributions', module)
    .addParameters({ jest: ['MyContributions'] })
    .add('default', () => <MyContributions {...props} />)
    .add('empty', () => <MyContributions ideas={[]} />);
