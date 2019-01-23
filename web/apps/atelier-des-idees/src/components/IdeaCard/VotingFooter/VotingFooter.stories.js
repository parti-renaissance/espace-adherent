import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import VotingFooter from '.';

const props = {
    totalVotes: 800,
    votes: [
        {
            id: 'important',
            name: 'Essentiel',
            count: 86,
            isSelected: false,
        },
        {
            id: 'feasible',
            name: 'Réalisable',
            count: 165,
            isSelected: true,
        },
        {
            id: 'innovative',
            name: 'Innovant',
            count: 1536,
            isSelected: false,
        },
    ],
};

storiesOf('VotingFooter', module)
    .addParameters({ jest: ['VotingFooter'] })
    .add('default', () => (
        <VotingFooter {...props} onSelected={action('selected vote')} onToggleVotePanel={action('toggle vote panel')} />
    ));
