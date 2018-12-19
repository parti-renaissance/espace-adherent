import React from 'react';
import { storiesOf } from '@storybook/react';
import LatestIdeas from '.';

storiesOf('LatestIdeas', module)
    .addParameters({ jest: ['LatestIdeas'] })
    .add('default', () => <LatestIdeas />);
