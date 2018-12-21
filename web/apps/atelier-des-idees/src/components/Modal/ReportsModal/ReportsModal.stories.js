import React from 'react';
import { storiesOf } from '@storybook/react';
import ReportsModal from '.';

const props = {
    reports: [
        {
            file: '/test',
            fileName: 'document-5.pdf',
            size: '1.2 Mb',
        },
        {
            file: '/test',
            fileName: 'document-5.pdf',
            size: '1.2 Mb',
        },
        {
            file: '/test',
            fileName: 'document-5.pdf',
            size: '1.2 Mb',
        },
        {
            file: '/test',
            fileName: 'document-5.pdf',
            size: '1.2 Mb',
        },
        {
            file: '/test',
            fileName: 'document-5.pdf',
            size: '1.2 Mb',
        },
    ],
};

storiesOf('ReportsModal', module)
    .addParameters({ jest: ['ReportsModal'] })
    .add('default', () => <ReportsModal {...props} />);
