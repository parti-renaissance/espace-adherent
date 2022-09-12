import React from 'react';
import {createRoot} from "react-dom/client";
import RenaissanceDonationWidget from '../components/Donation/RenaissanceDonationWidget';

export default (wrapperSelector) => createRoot(dom(wrapperSelector)).render(<RenaissanceDonationWidget />);
