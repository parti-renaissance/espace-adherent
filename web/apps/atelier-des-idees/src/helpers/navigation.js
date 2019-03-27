export function domainFromUrl(url) {
    let result;
    let match = url.match(/^(?:https?:\/\/)?(?:[^@\n]+@)?(?:www\.)?([^:/n?=]+)/im);
    if (match) {
        result = match[1];
        match = result.match(/^[^.]+\.(.+\..+)$/);
        if (match) {
            result = match[1];
        }
    }
    return result;
}

export function redirectToSignin() {
    window.location = `${window.location.href}?anonymous_authentication_intention=/connexion`;
}

export function keyPressed(e) {
    const srcElement = e.target; // get the element that fired the onkeydown function
    let dataset = false;
    let selectList = false;
    let next = '';
    let prev = '';
    if (srcElement.dataset) {
        // can we use HTML5 dataset?
        dataset = true; // remember for later
        // is this an element for which we care
        if ('true' === srcElement.dataset.selectlist) {
            selectList = true;
        }
    } else {
        // can't use HTML5 dataset, use getAttribute
        if ('true' === srcElement.getAttribute('data-selectlist')) {
            selectList = true;
        }
    }
    // is it a select element and the user pressed either up arrow or down arrow
    if (selectList && ('38' === e.keyCode || '40' === e.keyCode)) {
        // get the next and prev navigation options for this element
        if (dataset) {
            next = srcElement.dataset.next;
            prev = srcElement.dataset.prev;
        } else {
            next = srcElement.getAttribute('data-next');
            prev = srcElement.getAttribute('data-prev');
        }
        // up arrow was pressed and a prev element is defined
        if ('38' === e.keyCode && 'firstItem' !== prev) {
            document.getElementById(prev).focus();
        }
        // down arrow was pressed and a next element is defined
        if ('40' === e.keyCode && 'lastItem' !== next) {
            document.getElementById(next).focus();
        }
        // don't do native processing of the up or down arrow (page scrolling)
        e.preventDefault();
    }
}
