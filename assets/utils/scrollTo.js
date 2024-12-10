/**
 * scroll to element, scrollIntoView if element is higher than window
 *
 * @param {string} id
 */
export default function reScrollTo(id) {
    dom(`#${id}`).scrollIntoView({
        behavior: 'smooth',
        block: 'nearest',
        inline: 'nearest',
    });
}
