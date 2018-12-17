import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import Button from '.';

const props = {
    label: 'TEST',
};

storiesOf('Button', module)
    .addParameters({ jest: ['Button'] })
    .add('default', () => <Button {...props} onClick={action('click')}/>);
