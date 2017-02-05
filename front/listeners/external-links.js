/*
 * Transform all the links to different hosts into target blank links.
 */
export default () => {
    const isInternalLink = new RegExp(`/${window.location.host}/`);

    findAll(document, 'a').forEach((link) => {
        if (!isInternalLink.test(link.href)) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        }
    });
};
