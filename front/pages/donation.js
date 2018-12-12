import React from 'react';
import { render } from 'react-dom';
import DonationAmountChooser from '../components/DonationAmountChooser';

/*
 * Donation
 */
export default () => {
    const amountInput = dom('#donation-amount');
    const name = amountInput.name;
    const abonnementInput = dom('#donation-abonnement');
    const abonnementValue = abonnementInput.checked;
    const value = parseFloat(amountInput.value.replace(',', '.'));
    const chooser = document.createElement('div');

    insertAfter(amountInput, chooser);
    remove(amountInput);
    remove(abonnementInput);

    render(
        <DonationAmountChooser
            name={name}
            value={value}
            abonnement={abonnementValue}
        />,
        chooser
    );
};
