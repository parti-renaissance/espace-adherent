/*
 * Legislatives candidates map
 */
export default (mapFactory, api) => {
    api.getCandidates((candidates) => {
        const map = mapFactory.createMap(dom('#map'), {
            center: { lat: 46.7699, lng: 2.4279 }, // France coordinates
            streetViewControl: false,
            zoom: 5,
        });

        let infowindow = null;

        candidates.forEach((candidate) => {
            const marker = mapFactory.addMarker(map, {
                title: candidate.name,
                position: candidate.position,
            });

            google.maps.event.addListener(marker, 'click', () => {
                if (infowindow) {
                    infowindow.close();
                }

                infowindow = new google.maps.InfoWindow({
                    content: `<a href="${candidate.url}" target="_blank" class="candidate__overlay">
                              <div style="background-image: url('${candidate.picture}')"></div>
                              <h3>${candidate.name}</h3>
                              ${candidate.district}</a>`,
                    position: candidate.position,
                    pixelOffset: new google.maps.Size(0, -8),
                });

                infowindow.open(map);
            });
        });
    });
};
