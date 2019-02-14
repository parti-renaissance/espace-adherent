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
