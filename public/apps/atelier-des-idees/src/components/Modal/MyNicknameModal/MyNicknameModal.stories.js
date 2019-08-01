import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import MyNicknameModal from '.';

storiesOf('MyNicknameModal', module)
    .addParameters({ jest: ['MyNicknameModal'] })
    .add('default', () => <MyNicknameModal onSubmit={action('Submit nickname')} />)
    .add('with default values', () => (
        <MyNicknameModal onSubmit={action('Submit nickname')} defaultValues={{ nickname: 'bgdu06' }} />
    ))
    .add('success', () => <MyNicknameModal onSubmit={action('Submit nickname')} isSubmitSuccess={true} />)
    .add('error', () => (
        <MyNicknameModal
            onSubmit={action('Submit nickname')}
            defaultValues={{ nickname: 'bgdu06' }}
            isSubmitError={true}
        />
    ));
