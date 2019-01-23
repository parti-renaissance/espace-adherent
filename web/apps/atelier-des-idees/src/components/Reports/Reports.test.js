import React from 'react';
import TestRenderer from 'react-test-renderer';
import Reports from '.';

describe('Reports', () => {
    it('renders correctly', () => {
        const tree = TestRenderer.create(<Reports />).toJSON();
        expect(tree).toMatchSnapshot();
    });
});
