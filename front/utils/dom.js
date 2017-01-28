window.dom = (selector) => {
    return find(document, selector);
};

window.find = (element, selector) => {
    return element.querySelector(selector);
};

window.findAll = (element, selector) => {
    return element.querySelectorAll(selector);
};

window.on = (element, event, handler) => {
    return element.addEventListener(event, handler);
};

window.insertAfter = (element, newElement) => {
    if (element.parentNode) {
        element.parentNode.insertBefore(newElement, element.nextSibling);
    }
};

window.remove = (element) => {
    element.parentNode.removeChild(element);
};
