import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import Button from '.';

const props = {
  label: 'Je propose',
  icon: '/assets/img/icn_20px_comments.svg',
  classIcon: 'start' // start or end
};

storiesOf('Button', module)
  .addParameters({ jest: ['Button'] })
  .add('default/primary', () => <Button {...props} onClick={action('click')} />)
  .add('secondary', () => (
    <Button {...props} mode="secondary" onClick={action('click')} />
  ))
  .add('tertiary', () => (
    <Button {...props} mode="tertiary" onClick={action('click')} />
  ))
  .add('primary:loading', () => (
    <Button {...props} onClick={action('click')} isLoading={true} />
  ))
  .add('secondary:loading', () => (
    <Button
      {...props}
      mode="secondary"
      onClick={action('click')}
      isLoading={true}
    />
  ))
  .add('tertiary:loading', () => (
    <Button
      {...props}
      mode="tertiary"
      onClick={action('click')}
      isLoading={true}
    />
  ));
