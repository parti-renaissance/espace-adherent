/*
 * Transform all the links to different hosts into target blank links.
 */
export default () => {
    const isInternalLink = new RegExp(`/${window.location.host}/`);
    const isMailtoLink = new RegExp('mailto');

    findAll(document, 'a').forEach((link) => {
        if (!isInternalLink.test(link.href) && !isMailtoLink.test(link.href)) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        }
    });
};
