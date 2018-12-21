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
/**
 * Build request action type
 * @param {string} base Action's type
 */
export function createRequestTypes(base) {
    return ['REQUEST', 'SUCCESS', 'FAILURE'].reduce((acc, type) => {
        acc[type] = `${base}_${type}`;
        return acc;
    }, {});
}
