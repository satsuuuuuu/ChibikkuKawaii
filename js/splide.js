document.addEventListener('DOMContentLoaded', function () {
    // Initialize Hero Carousel
    new Splide('#splide-hero', {
        type: 'loop',
        perPage: 1,
        autoplay: true,
        interval: 5000,
        arrows: true,
        pagination: true,
        breakpoints: {
            768: {
                arrows: false,
                pagination: false,
            }
        }
    }).mount();

    // Initialize Featured Anime Carousel
    new Splide('#splide-featured', {
        type: 'loop',
        perPage: 4,
        perMove: 4,
        gap: '1rem',
        rewind: true,
        autoplay: true,
        interval: 7000,
        arrows: true,
        pagination: true,
        breakpoints: {
            1024: {
                perPage: 3,
                perMove: 3,
            },
            768: {
                perPage: 2,
                perMove: 2,
            },
            480: {
                perPage: 1,
                perMove: 1,
            },
        },
    }).mount();
});
updateCart();


