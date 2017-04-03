/*
 * Upcoming events map
 */
export default (mapFactory, api) => {
    api.getUpcomingEvents((events) => {
        const map = mapFactory.createMap(dom('#map'), {
            center: { lat: 46.7699, lng: 2.4279 }, // France coordinates
            streetViewControl: false,
            zoom: 5,
        });

        let infowindow = null;

        events.forEach((event) => {
            const marker = mapFactory.addMarker(map, {
                title: event.name,
                position: event.position,
            });

            google.maps.event.addListener(marker, 'click', () => {
                if (infowindow) {
                    infowindow.close();
                }

                let content = `<a href="${event.url}" target="_blank">${event.name}</a><br>Organisé par`;

                if ('committee_url' in event) {
                    content += `<a href="${event.committee_url}" target="_blank">${event.committee_name}</a>`;
                } else {
                    content += `&nbsp;${event.organizer}`;
                }

                infowindow = new google.maps.InfoWindow({
                    content,
                    position: event.position,
                    pixelOffset: new google.maps.Size(0, -8),
                });

                infowindow.open(map);
            });
        });
    });
};
