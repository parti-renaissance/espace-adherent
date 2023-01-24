const zoomSvg = () => {
    const svg = findOne(document, '#svg');
    const plusZoom = findOne(document, '#plus-zoom');
    const subZoom = findOne(document, '#sub-zoom');
    let viewBox = {
        x: 0,
        y: 0,
        w: svg.clientWidth * 0.75,
        h: svg.clientHeight * 0.75,
    };

    svg.setAttribute('viewBox', `${viewBox.x} ${viewBox.y} ${viewBox.w} ${viewBox.h}`);
    const svgSize = { w: svg.clientWidth, h: svg.clientHeight };

    on(plusZoom, 'click', (e) => {
        e.preventDefault();
        const { w, h } = viewBox;
        const dw = w * 0.05;
        const dh = h * 0.05;
        viewBox = {
            x: viewBox.x,
            y: viewBox.y,
            w: viewBox.w - dw,
            h: viewBox.h - dh,
        };
        svg.setAttribute('viewBox', `${viewBox.x} ${viewBox.y} ${viewBox.w} ${viewBox.h}`);
    });

    on(subZoom, 'click', (e) => {
        e.preventDefault();
        const { w, h } = viewBox;
        const mx = e.offsetX; // button x
        const my = e.offsetY;
        const dw = w * 2 * 0.05;
        const dh = h * 2 * 0.05;
        const dx = dw * (mx / svgSize.w);
        const dy = dh * (my / svgSize.h);
        viewBox = {
            x: viewBox.x + dx,
            y: viewBox.y + dy,
            w: viewBox.w + dw,
            h: viewBox.h + dh,
        };
        svg.setAttribute('viewBox', `${viewBox.x} ${viewBox.y} ${viewBox.w} ${viewBox.h}`);
    });
};

/**
 *
 * @param {HTMLElement} element
 * @param {int} id
 * @param {string | null} slug
 */
const currentSection = (element, id, slug = null) => {
    findAll(document, '.current').forEach((item) => removeClass(item, 'current'));
    findAll(element, '.invalid').forEach((item) => removeClass(item, 'invalid'));

    if (id !== undefined) {
        const validClass = 'string' === typeof slug ? 'current' : 'invalid';
        const path = findOne(document, `#dpt-${id}`);
        const item = findOne(document, `#list-${id}`);
        const departments = findOne(document, '#departments');

        addClass(path, validClass);
        if (null !== item) {
            addClass(item, 'current');
            departments.scrollTo({
                behavior: 'smooth',
                top: item.offsetTop - 500,
            });
        }
    }
};

export default (departments = {}) => ({
    display: false,
    departments,
    paths: [],
    links: [],
    search: '',

    init() {
        const element = findOne(document, '#department-map');

        if (!element) {
            return;
        }

        const dpts = this.departments;
        this.paths = findAll(element, '.department-map a');
        this.links = findAll(element, '.departments-list a');

        this.paths.forEach((path) => {
            path.addEventListener('mouseenter', function (e) {
                e.preventDefault();
                const id = this.id.replace('dpt-', '');
                currentSection(element, id, dpts[id].site_slug);
            });

            path.addEventListener('click', function (e) {
                e.preventDefault();

                const id = this.id.replace('dpt-', '');
                const department = dpts[id];

                if (null !== department.site_slug) {
                    window.open(`${element.dataset.federationPath}/${department.site_slug}`, '_blank');
                }
            });
        });
        zoomSvg();
    },

    get searchResults() {
        return Object.entries(this.departments)
            .filter(([k, v]) => v.name.startsWith(this.search) || k === this.search);
    },

    cleanSearch(e) {
        this.search = '';
    },
});
