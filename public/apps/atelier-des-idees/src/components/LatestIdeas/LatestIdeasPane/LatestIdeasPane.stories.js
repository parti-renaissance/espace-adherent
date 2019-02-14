import React from 'react';
import { storiesOf } from '@storybook/react';
import LatestIdeasPane from '.';

const ideas = [{ id: '000', title: 'Super proposition 1' }, { id: '111', title: 'Super proposition 2' }];

storiesOf('LatestIdeasPane', module)
    .addParameters({ jest: ['LatestIdeas'] })
    .add('default', () => <LatestIdeasPane link="/link" />)
    .add('with ideas', () => <LatestIdeasPane ideas={ideas} link="/link" />)
    .add('loading', () => <LatestIdeasPane isLoading={true} link="/link" />);
