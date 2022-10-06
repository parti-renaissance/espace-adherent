import React from 'react';
import { render } from 'react-dom';
import AdhesionTaxReturn from '../components/Adhesion/AdhesionTaxReturn';

export default () => {
    findAll(document, 'input.choice-widget').forEach((element) => {
        const value = parseFloat(element.value) || 0;
        const taxBlock = document.getElementById('amount-tax-return');

        on(element, 'click', (event) => {
            render(
                <AdhesionTaxReturn
                    value={value}
                />,
                taxBlock
            );
        });
    });
};
