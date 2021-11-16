/*
 * Transform all the links to different hosts into target blank links.
 */
export default () => {
    const isUrlLink = /http/;
    const isInternalLink = new RegExp(`/${window.location.host}/`);
    const isMailtoLink = /mailto/;

    findAll(document, 'a').forEach((link) => {
        if (isUrlLink.test(link.href) && !isInternalLink.test(link.href) && !isMailtoLink.test(link.href)) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        }
    });
};
