import React from 'react';
import { storiesOf } from '@storybook/react';
import ProposalSteps from '.';

storiesOf('ProposalSteps', module)
    .addParameters({ jest: ['ProposalSteps'] })
    .add('default', () => <ProposalSteps />);
