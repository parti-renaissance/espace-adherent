import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import VoteButton from '.';

const vote = {
    id: '000',
    count: 12,
    isSelected: false,
    name: 'Super',
};

storiesOf('VoteButton', module)
    .addParameters({ jest: ['VoteButton'] })
    .add('default', () => <VoteButton onSelected={action('vote selected')} vote={vote} />);
