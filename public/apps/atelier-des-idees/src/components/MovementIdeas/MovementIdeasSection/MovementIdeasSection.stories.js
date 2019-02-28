import React from 'react';
import { storiesOf } from '@storybook/react';
import MovementIdeasSection from '.';

const props = {
    keyWord: 'vote',
    title: 'pour des idéees',
    text:
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed fringilla urna sed erat auctor, ac sodales mi commodo.',
    linkLabel: 'Je vote',
    link: '/soutenir',
};

storiesOf('MovementIdeasSection', module)
    .addParameters({ jest: ['MovementIdeasSection'] })
    .add('default', () => <MovementIdeasSection {...props} />);
