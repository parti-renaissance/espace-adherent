import React from 'react';
import { storiesOf } from '@storybook/react';
import ContributingFooter from '.';

const props = {
    remainingDays: '6 jours restants',
    link: 'google.fr',
};

storiesOf('ContributingFooter', module)
    .addParameters({ jest: ['ContributingFooter'] })
    .add('default', () => <ContributingFooter {...props} />);