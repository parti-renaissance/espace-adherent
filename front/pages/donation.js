import React from 'react';
import { render } from 'react-dom';
import DonationWidget from '../components/DonationWidget';

export default (wrapperSelector) => {
    render(
        <DonationWidget />,
        dom(wrapperSelector)
    );
};
