import polylabel from '@mapbox/polylabel';

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

        map.data.setStyle({
            fillColor: '#ff4e42',
            strokeColor: '#c7736f',
            strokeWeight: 1,
        });

        map.data.addListener('mouseover', (e) => {
            map.data.revertStyle();
            map.data.overrideStyle(e.feature, {
                fillColor: 'green',
            });
        });

        map.data.addListener('mouseout', (e) => {
            map.data.revertStyle();
        });

        let infowindow = null;

        map.data.addListener('click', (e) => {
            const candidate = e.feature.getProperty('candidate');
            const center = polylabel(candidate.geojson.coordinates);
            const picture = candidate.picture ? candidate.picture : '/images/unknown-candidate-small.jpg';

            if (infowindow) {
                infowindow.close();
            }

            infowindow = new google.maps.InfoWindow({
                content: `<a href="${candidate.url}" target="_blank" class="candidate__overlay">
                              <div style="background-image: url('${picture}')"></div>
                              <h3>${candidate.name}</h3>
                              ${candidate.district}</a>`,
                position: {
                    lat: center[1],
                    lng: center[0],
                },
            });

            infowindow.open(map);
        });

        candidates.forEach((candidate) => {
            map.data.addGeoJson({
                type: 'Feature',
                geometry: candidate.geojson,
                properties: {
                    candidate,
                },
            });
        });
    });
};
