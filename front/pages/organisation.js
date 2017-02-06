/*
 * Organization page map
 */
export default (api) => {
    api.getReferents((referents) => {
        const map = new google.maps.Map(dom('#map'), {
            center: { lat: 46.7699, lng: 2.4279 },
            streetViewControl: false,
            scrollwheel: false,
            zoom: 5,
        });

        map.setOptions({
            styles: [{
                featureType: 'landscape',
                stylers: [
                    { hue: '#FFBB00' },
                    { saturation: 43.400000000000006 },
                    { lightness: 37.599999999999994 },
                    { gamma: 1 },
                ],
            }, {
                featureType: 'road.highway',
                stylers: [
                    { hue: '#FFC200' },
                    { saturation: -61.8 },
                    { lightness: 45.599999999999994 },
                    { gamma: 1 },
                ],
            }, {
                featureType: 'road.arterial',
                stylers: [
                    { hue: '#FF0300' },
                    { saturation: -100 },
                    { lightness: 51.19999999999999 },
                    { gamma: 1 },
                ],
            }, {
                featureType: 'road.local',
                stylers: [
                    { hue: '#FF0300' },
                    { saturation: -100 },
                    { lightness: 52 },
                    { gamma: 1 },
                ],
            }, {
                featureType: 'water',
                stylers: [
                    { hue: '#0078FF' },
                    { saturation: -13.200000000000003 },
                    { lightness: 2.4000000000000057 },
                    { gamma: 1 },
                ],
            }, {
                featureType: 'poi',
                stylers: [
                    { hue: '#00FF6A' },
                    { saturation: -1.0989010989011234 },
                    { lightness: 11.200000000000017 },
                    { gamma: 1 },
                ],
            }],
        });

        let infowindow = null;

        referents.forEach((referent) => {
            const marker = new google.maps.Marker({
                map,
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
                    content: referent.name,
                    position: {
                        lng: referent.coordinates[0],
                        lat: referent.coordinates[1],
                    },
                    pixelOffset: new google.maps.Size(0, -25),
                });

                infowindow.open(map);
            });
        });
    });
};
