/**
 * Build action's base structure
 * @param {string} type Action's type
 * @param {Object} payload Action's payload
 * @returns Full action body
 */
export function action(type, payload = {}) {
    return {
        type,
        payload,
    };
}
