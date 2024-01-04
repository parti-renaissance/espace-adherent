/** @typedef  {import('alpinejs').DirectiveCallback} AlpineDirectiveCallback */
/** @typedef  {import('alpinejs').ElementWithXAttributes} AlpineEl */
/**
 * First Step component for funnel
 * @type {AlpineDirectiveCallback}
 */
// eslint-disable-next-line no-empty-pattern
const numberOnly = (el, {}, { cleanup }) => {
    const handler = (e) => {
        const { value } = e.target;
        e.target.value = value.replace(/\D/g, '');
    };
    el.addEventListener('input', handler);
    cleanup(() => el.removeEventListener('input', handler));
};

export default numberOnly;
