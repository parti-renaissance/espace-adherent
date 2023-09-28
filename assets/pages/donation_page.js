import React from 'react';
import { createRoot } from 'react-dom/client';
import DonationWidget from '../components/Donation/DonationWidget';

export default () => {
    const amount = dom('input#amount').value;
    const duration = dom('input#duration').value;

    createRoot(dom('.renaissance-donation-widget-wrapper')).render(
        <DonationWidget defaultAmount={amount ? parseFloat(amount) : null} defaultDuration={duration} />
    );
};
