$('Document').ready(function() {

    $('.recently-added--container').slick({
        infinite: true,
        slidesToShow: 5,
        slidesToScroll: 3,
        appendArrows: ".recently-added",
        prevArrow: '<a href="#" class="slick-prev"><</a>',
        nextArrow: '<a href="#" class="slick-next">></a>'
    });

    $('.recently-updated--container').slick({
        infinite: true,
        slidesToShow: 5,
        slidesToScroll: 3,
        dots: true,
        appendArrows: ".recently-updated",
        appendDots: ".recently-updated",
        prevArrow: '<a href="#" class="slick-prev"><</a>',
        nextArrow: '<a href="#" class="slick-next">></a>'
    });
});
