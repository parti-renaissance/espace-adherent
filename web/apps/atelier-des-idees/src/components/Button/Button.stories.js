import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import Button from '.';

const props = {
    label: 'Je propose',
    icon: '/assets/img/icn_20px_comments.svg',
    className: 'button--secondary',
    classIcon: 'start', // start or end
};

storiesOf('Button', module)
    .addParameters({ jest: ['Button'] })
    .add('default', () => <Button {...props} onClick={action('click')}/>);
