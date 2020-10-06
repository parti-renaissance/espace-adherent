import React from 'react';
import flatpickr from 'flatpickr';
import { French } from 'flatpickr/dist/l10n/fr.js';

export default function () {
    // set french by default
    flatpickr.localize(French);

    findAll(document, '.em-datetime-picker').forEach((element) => {
        const options = JSON.parse(element.dataset.datetimepicker);
        flatpickr(element, options);
    });
}
