import React from 'react';
import { render } from 'react-dom';
import AmountChooser from '../components/AmountChooser';

export default () => {
    findAll(document, 'input.renaissance-amount-chooser').forEach((element) => {
        const { name, dataset } = element;
        const value = parseFloat(element.value) || 0;
        const chooser = document.createElement('div');

        insertAfter(element, chooser);
        remove(element);

        render(
            <AmountChooser
                name={name}
                value={value}
                displayLabel={'true' === dataset.displayLabel}
            />,
            chooser
        );
    });
};
