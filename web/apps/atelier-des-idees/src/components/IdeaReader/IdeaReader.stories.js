import React from 'react';
import { storiesOf } from '@storybook/react';
import IdeaReader from '.';

const paragraphs = [
    '<strong>Bonjour je voudrais plus de mousse au chocolat, parce que :</strong>',
    '<ul><li>C\'est bon</li><li>J\'aime le chocolat</li></ul>',
    '<p style="text-align: center;">Merci !</p>',
];

storiesOf('IdeaReader', module)
    .addParameters({ jest: ['IdeaReader'] })
    .add('default', () => <IdeaReader paragraphs={paragraphs} />);
