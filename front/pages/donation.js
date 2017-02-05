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

    const goStep1 = () => {
        hide(step2);
        show(step1);
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

    const error7500 = dom('#error-7500');
    const errorDecimals = dom('#error-decimals');
    const errorInvalid = dom('#error-invalid');

    const getPrecision = (a) => {
        if (!isFinite(a)) {
            return 0;
        }

        let e = 1;
        let p = 0;

        while (Math.round(a * e) / e !== a) {
            e *= 10;
            p += 1;
        }

        return p;
    };

    insertAfter(amountInput, chooser);
    remove(amountInput);

    render(
        <DonationAmountChooser
            name={name}
            value={value}
            onSubmit={goStep2}
            onChange={(amount) => {
                hide(nextStep);
                hide(errorInvalid);
                hide(error7500);
                hide(errorDecimals);

                amount = 'number' === typeof amount ? amount : parseFloat(amount.replace(',', '.'));

                if (!amount || 0 >= amount) {
                    show(errorInvalid);
                    return;
                }

                if (7500 < amount) {
                    show(error7500);
                    return;
                }

                if (2 < getPrecision(amount)) {
                    show(errorDecimals);
                    return;
                }

                show(nextStep);
            }}
        />,
        chooser
    );
};
