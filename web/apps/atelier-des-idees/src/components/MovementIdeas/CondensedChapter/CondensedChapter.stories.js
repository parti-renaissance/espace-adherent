import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import CondensedChapter from '.';
import icn_close from './../../../img/icn_20px_comments.svg';

const props = {
    title: [<span className="underline">Rédiger</span>, <br />, 'une propostion'],
    totalCount: 356,
    description: 'Soumettez une nouvelle proposition à la communauté. ',
    link: 'Je rédige',
};

storiesOf('CondensedChapter')
    .add('create', () => <CondensedChapter {...props} />)
    .add('contribute', () => (
        <CondensedChapter
            {...props}
            title={['Les propositions', <br />, 'à ', <span className="underline"> enrichir</span>]}
            description={[
                'Enrichissez une des ',
                <span className="total">{props.totalCount}</span>,
                ' propositions en cours d\'écriture.',
            ]}
        />
    ))

    .add('colaborate', () => (
        <CondensedChapter
            {...props}
            title={[<span className="underline">Voter</span>, <br />, 'les propositions']}
            description={[
                'Donnez votre avis sur les ',
                <span className="total">{props.totalCount}</span>,
                ' propositions finalisées.',
            ]}
        />
    ));
