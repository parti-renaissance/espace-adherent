export default class Carousel {
    /**
     * @param {HTMLElement} element
     * @param {Object} options
     * @param {Object} [options.slidesVisible=3] Le nombre d'éléments à faire afficher.
     * @param {Object} [options.slidesToScroll=1] Le nombre d'éléments à défiler.
     * @param {Object} [options.loop=false] Determine si le carousel scroll a l'infini.
     * @param {Object} [options.navigation=true] Permet de definir si la navigation s'affiche ou pas.
     */
    constructor(element, options = {}) {
        this.element = element;
        this.options = {
            slidesVisible: 1,
            slidesToScroll: 1,
            loop: false,
            navigation: true,
            ...options,
        };

        let children = [].slice.call(element.children);
        this.root = this.createDivElement('carousel');
        this.root.setAttribute('tabindex', '0');
        this.carouselInner = this.createDivElement('carousel-inner');
        this.currentSlide = 0;
        this.isMobile = true;
        this.onSlideCallback = [];

        this.root.appendChild(this.carouselInner);
        this.element.appendChild(this.root);
        this.element.classList.remove('grid', 'md:grid-cols-3', 'md:gap-8', 'gap-5');

        this.items = children.map((child) => {
            let item = this.createDivElement('carousel-inner__item');
            item.appendChild(child);
            this.carouselInner.appendChild(item);

            return item;
        });

        this.applyStyle();
        this.onWindowResize();

        if (this.options.navigation) {
            this.createNavigation();
        }

        this.onSlideCallback.forEach((callback) => callback(0));

        window.addEventListener('resize', this.onWindowResize.bind(this));
    }

    get slidesToScroll() {
        return this.isMobile ? 1 : this.options.slidesToScroll;
    }

    get slidesVisible() {
        return this.isMobile ? 1 : this.options.slidesVisible;
    }

    onWindowResize() {
        let mobile = 800 > window.innerWidth;
        if (mobile !== this.isMobile) {
            this.isMobile = mobile;
            this.applyStyle();
            this.onSlideCallback.forEach((callback) => callback(this.currentSlide));
        }
    }

    applyStyle() {
        let ratio = this.items.length / this.slidesVisible;
        this.carouselInner.style.width = `${ratio * 100}%`;
        this.carouselInner.style.display = 'flex';
        this.carouselInner.style.alignItems = 'center';
        this.items.forEach((item) => item.style.width = `${100 / this.slidesVisible / ratio}%`);
    }

    createNavigation() {
        const nextButton = this.createNavigationButton('carousel-navigation__button--next');
        const prevButton = this.createNavigationButton('carousel-navigation__button--prev');

        nextButton.append(this.renderSvgIcon('M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3'));
        prevButton.append(this.renderSvgIcon('M6.75 15.75L3 12m0 0l3.75-3.75M3 12h18'));

        this.root.appendChild(nextButton);
        this.root.appendChild(prevButton);

        nextButton.addEventListener('click', this.next.bind(this));
        prevButton.addEventListener('click', this.prev.bind(this));

        if (this.options.loop) {
            return;
        }

        this.onSlide((index) => {
            if (0 === index) {
                prevButton.classList.add('hidden');
            } else {
                prevButton.classList.remove('hidden');
            }

            if (this.items[this.currentSlide + this.slidesVisible] === undefined) {
                nextButton.classList.add('hidden');
            } else {
                nextButton.classList.remove('hidden');
            }
        });
    }

    next() {
        this.goToSlide(this.currentSlide + this.slidesToScroll);
    }

    prev() {
        this.goToSlide(this.currentSlide - this.slidesToScroll);
    }

    /**
     * @param {number} index
     */
    goToSlide(index) {
        if (0 > index) {
            index = this.items.length - this.slidesVisible;
        } else if (index >= this.items.length || (this.items[this.currentSlide + this.slidesVisible] === undefined && index > this.currentSlide)) {
            index = 0;
        }

        let translateX = index * -100 / this.items.length;
        this.carouselInner.style.transform = `translate3d(${  translateX  }%, 0, 0)`;
        this.currentSlide = index;
        this.onSlideCallback.forEach((callback) => callback(index));
    }

    /**
     * Enregistre les différents callback au slide des elements du carousel
     *
     * @param {Carousel~onSlideCallback} callback
     */
    onSlide(callback) {
        this.onSlideCallback.push(callback);
    }

    /**
     * @param {string} className
     * @returns {HTMLElement}
     */
    createNavigationButton(className) {
        let button = document.createElement('button');
        button.setAttribute('type', 'button');
        button.classList.add('carousel-navigation__button', className);

        return button;
    }

    /**
     * @param {string} path
     * @returns {SVGSVGElement}
     */
    renderSvgIcon(path) {
        const iconSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        const iconPath = document.createElementNS(
            'http://www.w3.org/2000/svg',
            'path'
        );

        iconSvg.setAttribute('fill', 'none');
        iconSvg.setAttribute('stroke', 'currentColor');
        iconSvg.setAttribute('stroke-width', '1.5');
        iconSvg.setAttribute('viewBox', '0 0 24 24');
        iconSvg.classList.add('w-6', 'h-6');

        iconPath.setAttribute('stroke-linecap', 'round');
        iconPath.setAttribute('stroke-linejoin', 'round');

        iconPath.setAttribute('d', path);
        iconSvg.appendChild(iconPath);

        return iconSvg;
    }

    /**
     * @param {string} className
     * @returns {HTMLElement}
     */
    createDivElement(className) {
        const rootElement = document.createElement('div');
        rootElement.classList.add(className);

        return rootElement;
    }
}
