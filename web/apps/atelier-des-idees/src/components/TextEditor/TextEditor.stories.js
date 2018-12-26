import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import TextEditor from '.';

const content = '<p><strong>Mes courses :</strong></p><ul><li>Tomates</li><li>Riz</li><li>Mousse au chocolat</li></ul>';

storiesOf('TextEditor', module)
    .addParameters({ jest: ['TextEditor'] })
    .add('default', () => <TextEditor onChange={action('editor change')} />)
    .add('with placeholder', () => <TextEditor placeholder="Coucou toi" onChange={action('editor change')} />)
    .add('with initial content', () => <TextEditor initialContent={content} onChange={action('editor change')} />);
