import React from 'react';
import { storiesOf } from '@storybook/react';
import ContributingFooter from '.';

const props = {
    remainingDays: '6',
    link: 'en-marche.fr',
};

storiesOf('IdeaCard/ContributingFooter', module)
    .addParameters({ jest: ['ContributingFooter'] })
    .add('default', () => <ContributingFooter {...props} />);
