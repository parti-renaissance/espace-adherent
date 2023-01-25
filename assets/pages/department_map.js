export default () => {
    // window.Alpine.data('departmentMap', departmentMap);
    const mapContainer = findOne(document, '#department-map');

    const paths = findAll(mapContainer, 'svg a');
    const links = findAll(mapContainer, '.departments-list a');

    paths.forEach((path) => {
        on(path, 'mouseenter', (e) => {
            e.preventDefault();
            const id = e.currentTarget.id.replace('dpt-', '');
            console.log(id);
            // currentSection(element, id, dpts[id].site_slug);
        });

        on(path, 'click', (e) => {
            e.preventDefault();

            const id = this.id.replace('dpt-', '');
            // // const department = dpts[id];
            //
            // if (null !== department.site_slug) {
            //     window.open(`${element.dataset.federationPath}/${department.site_slug}`, '_blank');
            // }
        });
    });
};
