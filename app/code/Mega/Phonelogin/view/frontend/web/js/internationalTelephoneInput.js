define([
    'jquery',
    'Mega/Phonelogin/view/frontend/web/js/intlTelInput'
], function ($) {
    var initIntl = function (config, node) {
        $(node).intlTelInput(config);
    };
    return initIntl;
});
