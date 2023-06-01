require(
    [
        "jquery",
        "Magento_Ui/js/modal/modal",
        'Magento_Ui/js/model/messageList',
        'mage/translate'
    ],
    function($,modal,messageList,$t){
        setTimeout(function(){
            require('Magento_Customer/js/customer-data').reload();
        }, 2000);
        $(document).on('click', '#mini-cart a.action.edit, .checkout-cart-index .action.action-edit', function(event){
            if ($("#edit-cart-main").length) {
                event.preventDefault();
                event.stopPropagation();
                var quoteid = 0,prdid = 0;
                var defult_url = $(this).attr('href');
                var spilt_string = defult_url.split('/');
                if ($.inArray("cart", spilt_string) != -1 ) {
                    var spilt_quoteid = $.inArray("id", spilt_string);
                    var spilt_prdid = $.inArray("product_id", spilt_string);
                    if ((spilt_quoteid >= 0)&&(spilt_prdid >= 0)) {
                        quoteid = spilt_string[spilt_quoteid + 1];
                        prdid = spilt_string[spilt_prdid + 1];
                    }
                }
                var ajax_url = $("#edit-cart-main").attr('data-ajax');
                $.ajax({
                    url: ajax_url,
                    type: 'post',
                    dataType: 'json',
                    context: '#edit-cart-main',
                    data: {quoteid:quoteid,prdid:prdid},
                    showLoader: true,
                    success: function(response){
                        if (response.error) {
                            messageList.addErrorMessage({ message: $t("add error message which you would require") });
                        } else{
                            $('#edit-cart-main').html(response.content);
                            var options = {
                                  modalClass: 'prd-cart-edit-popup',
                                  type: 'popup',
                                  responsive: true,
                                  innerScroll: true,
                                  title: response.name,
                                  buttons: [{
                                    text: $.mage.__('Cancel'),
                                    class: 'action-secondary action-dismiss',
                                    click: function () {
                                        this.closeModal();
                                    }
                                },
                                {
                                    text: $.mage.__('Update'),
                                    class: 'action confirm primary',
                                    click: function (data) {
                                        var dataForm = $("#form-edit-cart-popup"), form_data = dataForm.validation('isValid');
                                        if(form_data) {
                                            var edit_url = $("#form-edit-cart-popup").attr('action');
                                            var data = dataForm.serialize();
                                            var urlParams = edit_url+"?"+data;
                                            window.location.replace(urlParams);
                                            this.closeModal();
                                        }
                                    }
                                }]
                            };
                            var popup = modal(options, $('#edit-cart-main'));
                            $('#edit-cart-main').modal('openModal');
                        }
                    }
                });
            }
        });
})
