import Carousel from '../components/Carousel/Carousel';

export default () => {
    findAll(document, '.carousel-widget').forEach((element) => {
        const { dataset } = element;
        return new Carousel(element, {
            slidesVisible: dataset.carouselSlidesVisible ? dataset.carouselSlidesVisible : 3,
            slidesToScroll: dataset.carouselSlidesToScroll ? dataset.carouselSlidesToScroll : 1,
            loop: true,
        });
    });
};
