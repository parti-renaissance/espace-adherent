/*
 * Organization page map
 */
export default (mapFactory, api) => {
    const domMap = dom('#map');
    if (null === domMap) {
        return;
    }

    api.getReferents((referents) => {
        const map = mapFactory.createMap(domMap, {
            center: { lat: 46.7699, lng: 2.4279 },
            streetViewControl: false,
            scrollwheel: false,
            zoom: 5,
        });

        let infowindow = null;

        referents.forEach((referent) => {
            const marker = mapFactory.addMarker(map, {
                title: referent.name,
                position: {
                    lng: referent.coordinates[0],
                    lat: referent.coordinates[1],
                },
            });

            google.maps.event.addListener(marker, 'click', () => {
                if (infowindow) {
                    infowindow.close();
                }

                infowindow = new google.maps.InfoWindow({
                    content: `${referent.name} (${referent.postalCode})`,
                    position: {
                        lng: referent.coordinates[0],
                        lat: referent.coordinates[1],
                    },
                    pixelOffset: new google.maps.Size(0, -8),
                });

                infowindow.open(map);
            });
        });
    });
};
