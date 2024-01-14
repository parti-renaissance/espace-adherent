/**
 * scroll to element, scrollIntoView if element is higher than window
 *
 * @param {string} id
 */
export default function reScrollTo(id) {
    /** @type {HTMLElement} */
    const nextStepEl = dom(`#${id}`);

    const { clientHeight } = nextStepEl;
    const { clientHeight: windowHeight } = document.documentElement;
    const isHigherOfWindow = clientHeight > windowHeight * 0.8;

    if (isHigherOfWindow) {
        window.scrollTo({
            top: nextStepEl.getBoundingClientRect().top + window.scrollY - 136,
            behavior: 'smooth',
        });
    } else {
        nextStepEl.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'nearest',
        });
    }
}
