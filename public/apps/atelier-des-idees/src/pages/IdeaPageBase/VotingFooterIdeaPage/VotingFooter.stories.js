import React from 'react';
import { storiesOf } from '@storybook/react';
import VotingFooterIdeaPage from '.';

storiesOf('VotingFooterIdeaPage', module)
    .addParameters({ jest: ['VotingFooterIdeaPage'] })
    .add('default', () => <VotingFooterIdeaPage />);
