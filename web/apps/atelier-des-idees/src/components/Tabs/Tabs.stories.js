import React from 'react';
import { storiesOf } from '@storybook/react';
import Tabs from '.';

const props = {
    panes: [
        {
            title: 'TABS1',
            component: () => <div>1</div>,
        },
        {
            title: 'TABS2',
            component: <div>2</div>,
        },
    ],
};

storiesOf('Tabs', module)
    .addParameters({ jest: ['Tabs'] })
    .add('default', () => <Tabs {...props} />);
