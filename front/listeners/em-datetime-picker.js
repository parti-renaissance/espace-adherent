import flatpickr from 'flatpickr';
import { French } from 'flatpickr/dist/l10n/fr';

export default () => {
    // set french by default
    flatpickr.localize(French);

    findAll(document, 'input.em-datetime-picker').forEach((element) => {
        const options = JSON.parse(element.dataset.datetimepicker);
        const mobileOptions = { disableMobile: true };

        flatpickr(element, Object.assign(options, mobileOptions));
    });
};
