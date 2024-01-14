NodeList.prototype.forEach = Array.prototype.forEach;

/**
 * @param {Document|Element} element
 * @param {string} selector
 * @returns {Element}
 */
window.findOne = (element, selector) => element.querySelector(selector);

window.dom = (selector) => findOne(document, selector);

/**
 * @param element
 * @param selector
 * @returns {NodeListOf<Element>}
 */
window.findAll = (element, selector) => element.querySelectorAll(selector);

window.on = (element, event, handler) => element.addEventListener(event, handler);

window.once = (element, event, handler) => element.addEventListener(event, handler, { once: true });

window.off = (element, event, handler) => element.removeEventListener(event, handler);

window.insertAfter = (element, newElement) => {
    if (element.parentNode) {
        element.parentNode.insertBefore(newElement, element.nextSibling);
    }
};

window.remove = (element) => {
    element.parentNode.removeChild(element);
};
