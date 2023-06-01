/**
 * This script is a simple wrapper that allows you to use OwlCarousel with Magento 2
 */

define([
    "jquery",
    "Rb_BrandReviewSlider/js/owl.carousel.min"
], function($){
    return function (config, element) {
        return $(element).owlCarousel(config);
    }
});