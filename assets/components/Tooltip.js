import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

export default (el, {
    modifiers: [placement, theme = ''],
    expression,
}) => {
    tippy(el, {
        content: expression,
        placement: placement ?? 'auto',
        theme: theme ?? undefined,
        trigger: 'click',
    });
    if (theme) return;
    el.classList.add('tooltip');
};
