import React from 'react';
import TestRenderer from 'react-test-renderer';
import Banner from '.';

describe('Banner', () => {
    let props;

    beforeEach(() => {
        props = {
            title: 'Répondez à notre consultation sur les retraites !',
            subtitle: 'Du 5 aout 2018 au 29 septembre 2018',
            linkLabel: 'Je participe (2 min)',
            link: 'http://google.fr',
            onClose: () => {},
        };
    });

    it('renders correctly', () => {
        const tree = TestRenderer.create(<Banner {...props} />).toJSON();
        expect(tree).toMatchSnapshot();
    });
});
