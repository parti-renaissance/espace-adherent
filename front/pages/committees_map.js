/* eslint-disable no-restricted-syntax */
/*
 * Committees map
 */
export default (mapFactory, api) => {
    api.getCommittees((committees) => {
        const map = mapFactory.createMap(dom('#map'), {
            center: { lat: 46.7699, lng: 2.4279 }, // France coordinates
            streetViewControl: false,
            zoom: 5,
        });

        const infowindow = new google.maps.InfoWindow();

        const committeeSetsMap = new Map();

        for (const committee of committees) {
            let committeeSet = [committee];

            const key = JSON.stringify(committee.position);

            if (committeeSetsMap.has(key)) {
                committeeSet = committeeSetsMap.get(key);
                committeeSet.push(committee);
            }

            committeeSetsMap.set(key, committeeSet);
        }

        const markers = [];

        committeeSetsMap.forEach((committeeSet) => {
            const marker = mapFactory.createMarker({
                position: committeeSet[0].position,
            });

            const contentLinks = [];

            committeeSet.forEach((committee) => {
                contentLinks.push(`<a href="${committee.url}" target="_blank">${committee.name}</a>`);
            });

            google.maps.event.addListener(marker, 'click', () => {
                infowindow.setContent(contentLinks.join('</br>&</br>'));
                infowindow.open(map, marker);
            });

            markers.push(marker);
        });

        mapFactory.createMarkerClusterer(map, markers);
    });
};
