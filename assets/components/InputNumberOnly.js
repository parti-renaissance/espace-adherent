/** @typedef  {import('alpinejs').DirectiveCallback} AlpineDirectiveCallback */
/** @typedef  {import('alpinejs').ElementWithXAttributes} AlpineEl */
/**
 * First Step component for funnel
 * @type {AlpineDirectiveCallback}
 */

const numberOnly = (el, { modifiers: [min, max] }, { cleanup }) => {
    const handler = (e) => {
        const { value } = e.target;
        if ('' !== value) return;
        e.target.value = value.replace(/\D/g, '');
    };
    const handleBlur = (e) => {
        const { value } = e.target;
        if ('' === value) return;
        const onlyNumbers = value.replace(/\D/g, '');
        let number = Number(onlyNumbers);
        if (!Number.isNaN(number) && '' !== onlyNumbers) {
            if (min && number < Number(min)) number = Number(min);
            if (max && number > Number(max)) number = Number(max);
        }
        e.target.value = Number.isNaN(number) ? '' : number;
        if (number.toString() !== value) {
            e.target.dispatchEvent(new Event('input'));
            e.target.dispatchEvent(new Event('change'));
        }
    };
    el.addEventListener('input', handler);
    el.addEventListener('change', handleBlur);
    cleanup(() => {
        el.removeEventListener('input', handler);
        el.removeEventListener('change', handleBlur);
    });
};

export default numberOnly;
