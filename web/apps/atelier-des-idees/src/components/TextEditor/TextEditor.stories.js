import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import TextEditor from '.';

storiesOf('TextEditor', module)
    .addParameters({ jest: ['TextEditor'] })
    .add('default', () => <TextEditor onChange={action('editor change')} />)
    .add('with placeholder', () => <TextEditor placeholder="Coucou toi" onChange={action('editor change')} />);
