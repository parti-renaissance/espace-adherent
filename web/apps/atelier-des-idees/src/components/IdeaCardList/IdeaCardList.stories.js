import React from 'react';
import { storiesOf } from '@storybook/react';
import IdeaCardList from '.';

const ideas = [{ id: '000', title: 'Super proposition 1' }, { id: '111', title: 'Super proposition 2' }];

storiesOf('IdeaCardList', module).add('default', () => <IdeaCardList ideas={ideas} />);
