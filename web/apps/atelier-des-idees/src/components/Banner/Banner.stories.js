import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import Banner from '.';

const props = {
    name: 'Répondez à notre consultation sur les retraites !',
    started_at: '2019-01-03T16:01:01.670Z',
    ended_at: '2019-01-03T16:01:01.670Z',
    linkLabel: 'Je participe',
    url: 'http://google.fr',
};

storiesOf('Banner', module)
    .addParameters({ jest: ['Banner'] })
    .add('default', () => <Banner {...props} onClose={action('close')} />)
    .add('with extra info', () => (
        <Banner {...props} response_time="2" onClose={action('close')} />
    ));
