import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import TextEditor from '.';

const content =
    '<p><strong>Mes courses :</strong></p><ul><li>Tomates</li><li>Riz</li><li>Mousse au chocolat</li></ul><p style="text-align: center">Centré</p>';

storiesOf('TextEditor', module)
    .addParameters({ jest: ['TextEditor'] })
    .add('default', () => <TextEditor onChange={action('editor change')} />)
    .add('with placeholder', () => <TextEditor placeholder="Coucou toi" onChange={action('editor change')} />)
    .add('with initial content', () => <TextEditor initialContent={content} onChange={action('editor change')} />)
    .add('with max length', () => (
        <TextEditor maxLength={10} onChange={action('editor change')} placeholder="10 characters max." />
    ))
    .add('with error', () => (
        <TextEditor
            maxLength={10}
            onChange={action('editor change')}
            placeholder="10 characters max."
            error="Veuillez remplir ce champ"
        />
    ));
