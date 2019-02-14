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
    .addDecorator(story => <div style={{ padding: '50px 0' }}>{story()}</div>)
    .add('default', () => <VoteButton onSelected={action('vote selected')} vote={vote} />)
    .add('selected', () => <VoteButton onSelected={action('vote selected')} vote={{ ...vote, isSelected: true }} />);
