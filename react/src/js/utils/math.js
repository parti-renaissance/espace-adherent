export const getPercentage = (element, total) => (0 === total ? 0 : Math.floor(element / total * 100));
