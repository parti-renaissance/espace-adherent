import Glider from 'glider-js';

export default () => {
    findAll(document, '.glider-carousel').forEach((element) => {
        const { dataset } = element;
        return new Glider(element, {
            slidesToShow: 1,
            slidesToScroll: 1,
            draggable: true,
            duration: 1,
            arrows: {
                prev: `${dataset.gliderClass} .glider-prev`,
                next: `${dataset.gliderClass} .glider-next`,
            },
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: dataset.carouselSlidesVisible ? dataset.carouselSlidesVisible : 1,
                        slidesToScroll: dataset.carouselSlidesToScroll ? dataset.carouselSlidesToScroll : 1,
                    },
                },
                {
                    breakpoint: 640,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                    },
                },
            ],
        });
    });
};
