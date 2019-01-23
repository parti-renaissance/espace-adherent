import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import Reports from '.';

storiesOf('Reports', module)
    .addParameters({ jest: ['Reports'] })
    .add('default', () => (
        <Reports onReportBtnClicked={action('list of reports')} />
    ));
