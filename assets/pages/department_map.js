import interact from 'interactjs';

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

zoomSvg();

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

const DepartmentMap = () => {
    const mapContainer = findOne(document, '#container-map');
    const element = findOne(document, '#department-map');
    const position = { x: 0, y: 0 };

    const paths = findAll(element, '.department-map a');
    const dpts = JSON.parse(mapContainer.dataset.departments);
    const links = findAll(mapContainer, '.departments-list a');
    const search = findOne(document, '#department-search');
    const typeInterval = 500;
    let typingTimer;

    paths.forEach((path) => {
        path.addEventListener('mouseenter', (e) => {
            e.preventDefault();
            const id = e.target.id.replace('dpt-', '');
            currentSection(element, id, dpts[id]?.site_slug);
        });

        path.addEventListener('click', (e) => {
            e.preventDefault();

            const id = path.getAttribute('id').replace('dpt-', '');
            const department = dpts[id];

            if (null !== department.site_slug) {
                window.open(`${element.dataset.federationPath}/${department.site_slug}`, '_blank');
            }
        });
    });

    search.addEventListener('keyup', () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            // eslint-disable-next-line no-plusplus
            for (let i = 0; i < links.length; i++) {
                if (links[i].textContent.toLowerCase().includes(search.value.toLowerCase())) {
                    links[i].classList.remove('hidden');
                } else {
                    links[i].classList.add('hidden');
                }
            }
        }, typeInterval);
    });

    interact('.draggable')
        .draggable({
            listeners: {
                move(event) {
                    position.x += event.dx;
                    position.y += event.dy;

                    event.target.style.transform = `translate(${position.x}px, ${position.y}px)`;
                },
            },
        })
        .resizable({
            edges: {
                top: true,
                left: true,
                bottom: true,
                right: true,
            },
            listeners: {
                move: (event) => {
                    let { x, y } = event.target.dataset;

                    x = (parseFloat(x) || 0) + event.deltaRect.left;
                    y = (parseFloat(y) || 0) + event.deltaRect.top;

                    Object.assign(event.target.style, {
                        width: `${event.rect.width}px`,
                        height: `${event.rect.height}px`,
                        transform: `translate(${x}px, ${y}px)`,
                    });

                    Object.assign(event.target.dataset, { x, y });
                },
            },
        });
};

export default DepartmentMap;
