import React from 'react';
import { render } from 'react-dom';

import DonationAmountChooser from '../components/DonationAmountChooser';

/*
 * Create amount chooser input for donation
 */
export default () => {
    let amountInput = dom('#app_donation_amount');

    if (!amountInput) {
        return;
    }

    let name = amountInput.name,
        value = parseInt(amountInput.value),
        chooser = document.createElement('div');

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
