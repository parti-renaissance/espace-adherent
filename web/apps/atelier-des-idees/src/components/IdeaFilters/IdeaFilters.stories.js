import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import IdeaFilters from '.';

storiesOf('IdeaFilters', module)
    .add('default', () => <IdeaFilters onFilterChange={action('filter change')} />)
    .add('FINALIZED', () => <IdeaFilters onFilterChange={action('filter change')} status="FINALIZED" />)
    .add('with default values', () => (
        <IdeaFilters
            onFilterChange={action('filter change')}
            defaultValues={{ name: 'lol', authorCategory: 'QG', 'order[publishedAt]': 'ASC' }}
        />
    ));
