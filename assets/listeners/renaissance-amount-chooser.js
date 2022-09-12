import React from 'react';
import { render } from 'react-dom';
import NewAmountChooser from "../components/NewAmountChooser";

export default () => {
    findAll(document, 'input.renaissance-amount-chooser').forEach((element) => {
        const { name } = element;
        const value = parseFloat(element.value) || 0;
        const chooser = document.createElement('div');

        insertAfter(element, chooser);
        remove(element);

        render(
            <NewAmountChooser
                name={name}
                value={value}
            />,
            chooser
        );
    });
};
