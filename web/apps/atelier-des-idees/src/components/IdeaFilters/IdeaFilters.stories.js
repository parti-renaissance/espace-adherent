import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';
import IdeaFilters from '.';

storiesOf('IdeaFilters', module).add('default', () => <IdeaFilters onFilterChange={action('filter change')} />);
