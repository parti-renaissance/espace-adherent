import React from 'react';
import { storiesOf } from '@storybook/react';
import ReportsModal from '.';

storiesOf('ReportsModal', module)
    .addParameters({ jest: ['ReportsModal'] })
    .add('default', () => <ReportsModal />);
