/** @typedef  {import('alpinejs').DirectiveCallback} AlpineDirectiveCallback */
/** @typedef  {import('alpinejs').ElementWithXAttributes} AlpineEl */

/**
 * @param {AlpineEl} el
 * @return {(e: Event)=>void
 */
const resizeTextArea = (el) => (e) => {
    el.style.height = 'auto';
    el.style.height = `${el.scrollHeight}px`;
};

/**
 * First Step component for funnel
 * @type {AlpineDirectiveCallback}
 */
// eslint-disable-next-line no-empty-pattern
const autogrow = (el, {}, { cleanup }) => {
    const handler = resizeTextArea(el);
    el.addEventListener('input', handler);
    el.addEventListener('change', handler);
    cleanup(() => el.removeEventListener('input', handler));
};

export default autogrow;
