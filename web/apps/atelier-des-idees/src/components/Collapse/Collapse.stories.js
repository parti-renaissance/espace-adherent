import React from 'react';
import { storiesOf } from '@storybook/react';
import Collapse from '.';

const props = {
    title: {
        component: () => (
            <div>5. Droit : Votre idée suppose t-elle de changer le droit ? </div>
        ),
    },
};

storiesOf('Collapse', module)
    .addParameters({ jest: ['Collapse'] })
    .add('default', () => <Collapse {...props} />)
    .add('with children', () => (
        <Collapse {...props}>
            <div>TEST</div>
        </Collapse>
    ));
