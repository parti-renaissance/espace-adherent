export const camelToSnakeCase = (str) => str.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);

export const snakeToCamelCase = (str) => str.toLowerCase().replace(/([-_][a-z])/g, (group) => group.replace('-', '').replace('_', ''));
export default {};
