import React from 'react';
import { storiesOf } from '@storybook/react';
import IdeaReader from '.';

const idea = {
    authorName: 'Laurent La France',
    createdAt: '28/12/2018',
    title: 'Avoir plus de mousse au chocolat à la cantine',
    paragraphs: [
        '<strong>Bonjour je voudrais plus de mousse au chocolat, parce que :</strong>',
        '<ul><li>C\'est bon</li><li>J\'aime le chocolat</li></ul>',
        '<p style="text-align: center;">Merci !</p>',
    ],
};

storiesOf('IdeaReader', module)
    .addParameters({ jest: ['IdeaReader'] })
    .add('default', () => <IdeaReader {...idea} />);
