import React from 'react';
import { storiesOf } from '@storybook/react';
import LatestIdeasPane from '.';

storiesOf('LatestIdeasPane', module)
    .addParameters({ jest: ['LatestIdeas'] })
    .add('default', () => <LatestIdeasPane link="/link" />)
    .add('loading', () => <LatestIdeasPane isLoading={true} link="/link" />);
