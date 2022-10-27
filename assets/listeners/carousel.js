import Carousel from '../components/Carousel/Carousel';

export default () => {
    findAll(document, '.carousel-widget').forEach((element) => new Carousel(element, {
        slidesVisible: 3,
        slidesToScroll: 3,
        loop: true,
    }));
};
