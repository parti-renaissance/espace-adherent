import React from 'react';
import TestRenderer from 'react-test-renderer';
import ReportsModal from '.';

describe('ReportsModal', () => {
    it('renders correctly', () => {
        const tree = TestRenderer.create(<ReportsModal />).toJSON();
        expect(tree).toMatchSnapshot();
    });
});
