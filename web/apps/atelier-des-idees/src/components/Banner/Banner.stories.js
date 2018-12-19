import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import Banner from '.';

const props = {
    title: 'Répondez à notre consultation sur les retraites !',
    subtitle: 'Du 5 aout 2018 au 29 septembre 2018',
    linkLabel: 'Je participe (2 min)',
    link: 'http://google.fr',
};

storiesOf('Banner', module)
    .addParameters({ jest: ['Banner'] })
    .add('default', () => <Banner {...props} onClose={action('close')} />);
