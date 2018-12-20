import React from 'react';
import { storiesOf } from '@storybook/react';
import IdeaCard from '.';

const props = {
    author: {
        name: 'Jean-Michel Français',
        type: {
            id: 'qg',
            text: 'LaREM',
        },
    },
    thumbnail: '/assets/img/icn_76px_education.svg',
    createdAt: new Date().toISOString(),
    nbComments: 122,
    nbContributors: 4,
    description:
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed molestie sapien eu orci congue sollicitudin. Sed malesuada nisi sed diam tristique, ullamcorper fermentum massa euismod. Vivamus a augue vitae nibh scelerisque…',
    tags: ['education', 'civil-rights'],
    title: 'Réduire le nombre d’élèves dans les classes dans les quartiers défavorisés',
};

storiesOf('IdeaCard', module)
    .addParameters({ jest: ['IdeaCard'] })
    .add('default', () => <IdeaCard {...props} />);
