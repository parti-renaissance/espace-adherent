/**
 *
 * @param {HTMLElement} elem
 * @param {int} id
 * @param {string | null} slug
 */
const currentSection = (elem, id, slug = null) => {
    elem.querySelectorAll('.current')
        .forEach((item) => item.classList.remove('current'));
    elem.querySelectorAll('.invalid')
        .forEach((item) => item.classList.remove('invalid'));

    if (id !== undefined) {
        const validClass = 'string' === typeof slug ? 'current' : 'invalid';
        document.querySelector(`#dpt-${id}`).classList.add(validClass);
    }
};

export default (departments = {}) => ({
    display: false,
    departments,
    paths: [],

    init() {
        const element = this.$el;
        const dpts = this.departments;
        this.paths = element.querySelectorAll('.map a');
        this.paths.forEach((path) => {
            // eslint-disable-next-line func-names
            path.addEventListener('mouseenter', function (e) {
                e.preventDefault();

                const id = this.id.replace('dpt-', '');
                currentSection(element, id, dpts[id].department_site_slug);
            });

            // eslint-disable-next-line func-names
            path.addEventListener('click', function (e) {
                e.preventDefault();

                const id = this.id.replace('dpt-', '');
                const department = dpts[id];

                if (null === department.department_site_slug) {
                    // eslint-disable-next-line no-alert
                    alert('Ce département ne possède pas encore de site.');
                } else {
                    // eslint-disable-next-line max-len
                    window.location.href = `https://${element.dataset.host}/federations/${department.department_site_slug}`;
                }
            });
        });
    },

    toggle() {
        this.display = !this.display;
    },
});
