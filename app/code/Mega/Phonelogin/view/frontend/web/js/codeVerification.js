/**
 * Created by simplysaif on 8/3/18.
 */
require([
    'jquery',
], function($) {
    $(document).ready(function () {
        $('#verification_code').keyup(function () {
            var num = $('#verification_code').val();
            console.log(num)
        });
    })
});