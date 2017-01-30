window.show = (element) => {
    element.style.display = 'block';
};

window.hide = (element) => {
    element.style.display = 'none';
};

window.addClass = (element, className) => {
    element.classList.add(className);
};

window.hasClass = (element, className) => {
    element.classList.contains(className);
};

window.toggleClass = (element, className) => {
    element.classList.toggle(className);
};

window.removeClass = (element, className) => {
    element.classList.remove(className);
};
