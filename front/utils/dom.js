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

window.toggleCLass = (element, className) => {
    if (element.classList) {
        element.classList.toggle(className);
    } else {
        const classes = element.className.split(' ');
        const index = classes.indexOf(className);

        if (-1 !== index) {
            classes.splice(index, 1);
        } else {
            classes.push(className);
        }

        element.className = classes.join(' ');
    }
};

window.removeClass = (element, className) => {
    if (element.classList) {
        element.classList.remove(className);
    } else {
        element.className.replace(className, '');
    }
};

window.addClass = (element, className) => {
    if (element.classList) {
        element.classList.add(className);
    } else {
        element.className += className;
    }
};
