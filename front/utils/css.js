window.show = (element) => {
    element.style.display = 'block';
};

window.hide = (element) => {
    element.style.display = 'none';
};

window.addClass = (element, className) => {
    if (element.classList) {
        element.classList.add(className);
    } else {
        element.className += ` ${className}`;
    }
};

window.hasClass = (element, className) => {
    if (element.classList) {
        return element.classList.contains(className);
    }

    return -1 !== element.className.indexOf(className);
};

window.removeClass = (element, className) => {
    if (element.classList) {
        element.classList.remove(className);
    } else {
        element.className.replace(className, '');
    }
};

window.toggleClass = (element, className) => {
    if (element.classList) {
        return element.classList.toggle(className);
    }

    if (hasClass(element, className)) {
        return removeClass(element, className);
    }

    return addClass(element, className);
};
