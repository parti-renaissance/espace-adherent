import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import Button from '.';
import icn_close from './../../img/icn_20px_comments';

const props = {
    label: 'Je propose',
    icon: { icn_close },
    classIcon: 'start', // start or end
};

storiesOf('Button', module)
    .addParameters({ jest: ['Button'] })
    .add('default/primary', () => <Button {...props} onClick={action('click')} />)
    .add('primary:disabled', () => <Button {...props} disabled={true} onClick={action('click')} />)
    .add('primary:loading', () => <Button {...props} onClick={action('click')} isLoading={true} />)
    .add('secondary', () => <Button {...props} mode="secondary" onClick={action('click')} />)
    .add('secondary:disabled', () => <Button {...props} mode="secondary" disabled={true} onClick={action('click')} />)
    .add('secondary:loading', () => <Button {...props} mode="secondary" onClick={action('click')} isLoading={true} />)
    .add('tertiary', () => <Button {...props} mode="tertiary" onClick={action('click')} />)
    .add('tertiary:disabled', () => <Button {...props} mode="tertiary" disabled={true} onClick={action('click')} />)
    .add('tertiary:loading', () => <Button {...props} mode="tertiary" onClick={action('click')} isLoading={true} />);
