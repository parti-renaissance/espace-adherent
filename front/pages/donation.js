import React from 'react';
import { render } from 'react-dom';
import DonationAmountChooser from '../components/DonationAmountChooser';

/*
 * Donation
 */
export default () => {
    // Two steps donation
    const step1 = dom('#step1');
    const step1Tab = dom('#step1-tab');
    const step2 = dom('#step2');
    const step2Tab = dom('#step2-tab');
    const nextStep = dom('#next-step');
    const nextStepButton = dom('#next-step-button');

    hide(step2);
    show(nextStep);

    const goStep1 = () => {
        hide(step2);
        show(step1);
        show(nextStep);
        removeClass(step2Tab, 'active');
        removeClass(step1Tab, 'clickable');
        addClass(step1Tab, 'active');
    };

    const goStep2 = () => {
        hide(step1);
        hide(nextStep);
        show(step2);
        removeClass(step1Tab, 'active');
        addClass(step1Tab, 'clickable');
        addClass(step2Tab, 'active');
    };

    on(nextStepButton, 'click', goStep2);
    on(step1Tab, 'click', (event) => {
        event.preventDefault();
        goStep1();
    });

    // Amount chooser
    const amountInput = dom('#app_donation_amount');
    const name = amountInput.name;
    const value = parseFloat(amountInput.value.replace(',', '.'));
    const chooser = document.createElement('div');

    insertAfter(amountInput, chooser);
    remove(amountInput);

    render(
        <DonationAmountChooser
            name={name}
            value={value}
            onSubmit={goStep2}
        />,
        chooser
    );
};
