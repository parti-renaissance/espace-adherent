import React from 'react';
import { render } from 'react-dom';
import AmountChooser from '../components/AmountChooser';

/*
 * Create amount chooser inputs
 */
export default () => {
    findAll(document, '.amount-chooser').forEach((element) => {
        const name = element.name;
        const value = parseInt(element.value, 10);
        const chooser = document.createElement('div');

        insertAfter(element, chooser);
        remove(element);

        render(
            <AmountChooser
                name={name}
                value={value}
            />,
            chooser
        );
    });
};
