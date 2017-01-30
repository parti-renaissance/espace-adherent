import React from 'react';
import { render } from 'react-dom';

import DonationAmountChooser from '../components/DonationAmountChooser';

/*
 * Create amount chooser input for donation
 */
export default () => {
    const amountInput = dom('#app_donation_amount');

    if (!amountInput) {
        return;
    }

    const name = amountInput.name;
    const value = parseInt(amountInput.value, 10);
    const chooser = document.createElement('div');

    insertAfter(amountInput, chooser);
    remove(amountInput);

    render(
        <DonationAmountChooser
            name={name}
            value={value}
        />,
        chooser
    );
};
