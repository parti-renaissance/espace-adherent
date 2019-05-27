/**
 * Format number:
 *   `12345` -> `12 345`
 *   `12345123123` -> `12 345 123 123`
 * @param number
 * @returns {string}
 */
export default function numberFormat(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}
