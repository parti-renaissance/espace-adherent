/*
 * Commitees map
 */
export default (mapFactory, api) => {
    api.getCommittees((committees) => {
        const map = mapFactory.createMap(dom('#map'), {
            center: { lat: 46.7699, lng: 2.4279 },
            streetViewControl: false,
            zoom: 5,
        });

        let infowindow = null;

        committees.forEach((committee) => {
            const marker = mapFactory.addMarker(map, {
                title: committee.name,
                position: committee.position,
            });

            google.maps.event.addListener(marker, 'click', () => {
                if (infowindow) {
                    infowindow.close();
                }

                infowindow = new google.maps.InfoWindow({
                    content: `<a href="${committee.url}" target="_blank">${committee.name}</a>`,
                    position: committee.position,
                    pixelOffset: new google.maps.Size(0, -8),
                });

                infowindow.open(map);
            });
        });
    });
};
