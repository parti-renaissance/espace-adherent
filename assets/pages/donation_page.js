import React from 'react';
import { createRoot } from 'react-dom/client';
import DonationWidget from '../components/Donation/DonationWidget';

export default () => createRoot(dom('.renaissance-donation-widget-wrapper')).render(<DonationWidget />);
