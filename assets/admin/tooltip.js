import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

const setupTooltips = () => {
    tippy('[data-tippy-content]', {
        allowHTML: true,
        placement(reference) {
            return reference.getAttribute('data-tippy-placement') || 'top';
        },
    });
};

export default setupTooltips;
