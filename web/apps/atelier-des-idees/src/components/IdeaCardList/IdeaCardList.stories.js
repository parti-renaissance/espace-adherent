import React from 'react';
import { storiesOf } from '@storybook/react';
import IdeaCardList from '.';

const idea = {
    author: {
        first_name: 'Jean-Michel',
        last_name: 'Français',
    },
    author_category: 'QG',
    created_at: new Date().toISOString(),
    comments_count: 122,
    contributors_count: 4,
    description:
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed molestie sapien eu orci congue sollicitudin. Sed malesuada nisi sed diam tristique, ullamcorper fermentum massa euismod. Vivamus a augue vitae nibh scelerisque…',
    themes: [
        { name: 'Education', thumbnail: '/assets/img/icn_76px_education.svg' },
        { name: 'Droits civiques', thumbnail: null },
    ],
    category: { name: 'Education', enabled: true },
    name: 'Réduire le nombre d’élèves dans les classes dans les quartiers défavorisés',
    status: 'PENDING',
    days_before_deadline: 6,
    votes_count: {
        important: 10,
        feasible: 20,
        innovative: 30,
        total: 60,
        my_votes: ['feasible', 'important'],
    },
};

const ideas = [{ uuid: '000', ...idea }, { uuid: '111', ...idea, status: 'FINALIZED' }];

storiesOf('IdeaCardList', module)
    .add('default', () => <IdeaCardList ideas={ideas} />)
    .add('grid mode', () => <IdeaCardList ideas={ideas} mode="grid" />)
    .add('loading', () => <IdeaCardList ideas={ideas} isLoading={true} />);
