import React from 'react';
import { storiesOf } from '@storybook/react';
import IdeaCardSkeletonList from '.';

storiesOf('IdeaCardSkeletonList', module)
    .addDecorator(story => <div style={{ background: '#fafbfc' }}>{story()}</div>)
    .add('default', () => <IdeaCardSkeletonList />)
    .add('with nbItems', () => <IdeaCardSkeletonList nbItems={5} />)
    .add('grid mode', () => <IdeaCardSkeletonList nbItems={5} mode="grid" />);
