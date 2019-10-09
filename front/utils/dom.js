NodeList.prototype.forEach = Array.prototype.forEach;

window.dom = selector => find(document, selector);

window.find = (element, selector) => element.querySelector(selector);

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
