import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

export default (el, { modifiers, expression }) => {
    tippy(el, {
        content: expression,
        placement: modifiers[0] ?? 'auto',
    });
    el.classList.add('tooltips');
};
