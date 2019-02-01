import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import FlagModal from '.';

const props = {
    reasons: [
        {
            value: 'en_marche_values',
            label: 'Ce que je vois ne correspond pas aux valeurs du Mouvement',
        },
        { value: 'inappropriate', label: 'Ce n\'est pas du contenu approprié' },
        { value: 'commercial_content', label: 'Il s\'agit de contenu commercial' },
        { value: 'other', label: 'Autre' },
    ],
};

storiesOf('FlagModal', module)
    .addParameters({ jest: ['FlagModal'] })
    .add('default', () => <FlagModal {...props} onSubmit={action('Flag')} />)
    .add('success', () => <FlagModal {...props} onSubmit={action('Flag')} isSubmitSuccess={true} />)
    .add('error', () => <FlagModal {...props} onSubmit={action('Flag')} isSubmitError={true} />);
