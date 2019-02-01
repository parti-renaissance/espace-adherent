import React from 'react';
import { storiesOf } from '@storybook/react';
import MyNicknameModal from '.';

storiesOf('MyNicknameModal', module)
    .addParameters({ jest: ['MyNicknameModal'] })
    .add('default', () => <MyNicknameModal />)
    .add('success', () => <MyNicknameModal isSubmitSuccess={true} />)
    .add('error', () => <MyNicknameModal isSubmitError={true} />);
