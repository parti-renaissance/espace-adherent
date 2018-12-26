import React from 'react';
import { storiesOf } from '@storybook/react';
import TextEditor from '.';

storiesOf('TextEditor', module)
    .addParameters({ jest: ['TextEditor'] })
    .add('default', () => <TextEditor />)
    .add('with placeholder', () => <TextEditor placeholder="Coucou toi" />);
