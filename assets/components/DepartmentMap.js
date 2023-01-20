const zoomSvg = () => {
    const svg = document.getElementById('svg');
    const plusZoom = document.getElementById('plus-zoom');
    const subZoom = document.getElementById('sub-zoom');
    let viewBox = {
        x: 0,
        y: 0,
        w: svg.clientWidth * 0.75,
        h: svg.clientHeight * 0.75,
    };

    svg.setAttribute('viewBox', `${viewBox.x} ${viewBox.y} ${viewBox.w} ${viewBox.h}`);
    const svgSize = { w: svg.clientWidth, h: svg.clientHeight };

    plusZoom.addEventListener('click', (e) => {
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
    subZoom.addEventListener('click', (e) => {
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

zoomSvg();

/**
 *
 * @param {HTMLElement} elem
 * @param {int} id
 * @param {string | null} slug
 */
const currentSection = (elem, id, slug = null) => {
    document.querySelectorAll('.current')
        .forEach((item) => item.classList.remove('current'));
    elem.querySelectorAll('.invalid')
        .forEach((item) => item.classList.remove('invalid'));

    if (id !== undefined) {
        const validClass = 'string' === typeof slug ? 'current' : 'invalid';
        const path = document.querySelector(`#dpt-${id}`);
        const item = document.querySelector(`#list-${id}`);
        const departments = document.getElementById('departments');

        path.classList.add(validClass);
        item.classList.add('current');
        departments.scrollTo({
            behavior: 'smooth',
            top: item.offsetTop - 500,
        });
    }
};

export default (departments = {}) => ({
    display: false,
    departments,
    paths: [],
    links: [],
    search: '',

    init() {
        const element = document.getElementById('map');
        const dpts = this.departments;
        this.paths = element.querySelectorAll('.map a');
        this.links = element.querySelectorAll('.departments-list a');

        this.paths.forEach((path) => {
            // eslint-disable-next-line func-names
            path.addEventListener('mouseenter', function (e) {
                e.preventDefault();
                const id = this.id.replace('dpt-', '');
                currentSection(element, id, dpts[id].department_site_slug);

                this.search = '';
            });

            // eslint-disable-next-line func-names
            path.addEventListener('click', function (e) {
                e.preventDefault();

                const id = this.id.replace('dpt-', '');
                const department = dpts[id];

                if (null !== department.department_site_slug) {
                    // eslint-disable-next-line max-len
                    window.open(`federations/${department.department_site_slug}`, '_blank');
                }
            });
        });
    },

    get searchResults() {
        return Object.entries(this.departments)
            .filter(([k, v]) => v.department_name.startsWith(this.search) || k === this.search);
    },

    toggle() {
        this.display = !this.display;
    },

    cleanSearch(e) {
        this.search = '';
    },
});
