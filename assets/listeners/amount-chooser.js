import React from 'react';
import { createRoot } from 'react-dom/client';
import AmountChooser from '../components/AmountChooser';

const defaultAmounts = [
    { label: 'Tarif réduit <sup class="text-red-500">(1)</sup>', amount: 10 },
    { label: 'Tarif normal', amount: 30 },
    { label: 'Tarif normal<br>avec don', amount: 60 },
    { label: 'Tarif normal<br>avec don', amount: 120 },
    { label: 'Tarif normal<br>avec don', amount: 500 },
];

export default () => {
    findAll(document, 'input.renaissance-amount-chooser').forEach((element) => {
        const { name, dataset } = element;
        const value = parseFloat(element.value) || 0;
        const chooser = document.createElement('div');

        insertAfter(element, chooser);
        remove(element);

        createRoot(chooser).render(
            <AmountChooser
                name={name}
                value={value}
                displayLabel={'true' === dataset.displayLabel}
                minValue={'true' === dataset.adhesion ? 30.0 : 1.0}
                amounts={
                    dataset.readhesion
                        ? defaultAmounts
                        : defaultAmounts.map((item) => ({
                              ...item,
                              label: `${item.label}  <sup class="text-red-500">(2)</sup>`,
                          }))
                }
            />
        );
    });
};
