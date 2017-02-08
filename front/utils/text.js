window.trim = string => string.replace(/(^\s*|\s*$)/g, '');

window.startsWith = (haystack, needle) => 'string' === typeof haystack && 0 === haystack.indexOf(needle);
