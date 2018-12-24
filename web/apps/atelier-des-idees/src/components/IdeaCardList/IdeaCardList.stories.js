import React from 'react';
import { storiesOf } from '@storybook/react';
import IdeaCardList from '.';

const idea = {
    author: {
        first_name: 'Jean-Michel',
        last_name: 'Français',
    },
    author_category: 'QG',
    thumbnail: '/assets/img/icn_76px_education.svg',
    created_at: new Date().toISOString(),
    comments_count: 122,
    contributors_count: 4,
    description:
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed molestie sapien eu orci congue sollicitudin. Sed malesuada nisi sed diam tristique, ullamcorper fermentum massa euismod. Vivamus a augue vitae nibh scelerisque…',
    theme: { name: 'Droits civiques' },
    category: { name: 'Education' },
    name: 'Réduire le nombre d’élèves dans les classes dans les quartiers défavorisés',
    status: 'PENDING',
};

const ideas = [{ id: '000', ...idea }, { id: '111', ...idea }];

storiesOf('IdeaCardList', module)
    .add('default', () => <IdeaCardList ideas={ideas} />)
    .add('grid mode', () => <IdeaCardList ideas={ideas} mode="grid" />)
    .add('loading', () => <IdeaCardList ideas={ideas} isLoading={true} />);
